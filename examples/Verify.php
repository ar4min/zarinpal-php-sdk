<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Http\Client\Common\Plugin\HeaderDefaultsPlugin;
use ZarinPal\Sdk\ClientBuilder;
use ZarinPal\Sdk\Options;
use ZarinPal\Sdk\ZarinPal;
use ZarinPal\Sdk\Endpoint\PaymentGateway\RequestTypes\VerifyRequest;


$clientBuilder = new ClientBuilder();
$clientBuilder->addPlugin(new HeaderDefaultsPlugin([
    'Accept' => 'application/json',
]));

$options = new Options([
    'client_builder' => $clientBuilder,
    'sandbox' => false,
    'merchant_id' => 'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx',
]);

$zarinpal = new ZarinPal($options);
$paymentGateway = $zarinpal->paymentGateway();

$authority = $_GET['Authority'];
$status = $_GET['Status'];

if ($status === 'OK') {

    $amount = getAmountFromDatabase($authority); // تابع فرضی برای دریافت مبلغ از دیتابیس

    if ($amount) {
        $verifyRequest = new VerifyRequest();
        $verifyRequest->authority = $authority;
        $verifyRequest->amount = $amount;

        try {
            $response = $paymentGateway->verify($verifyRequest);

            if ($response->code === 100 || $response->code === 101) {
                echo "Payment Verified: \n";
                echo "Reference ID: " . $response->ref_id . "\n";
                echo "Card PAN: " . $response->card_pan . "\n";
                echo "Fee: " . $response->fee . "\n";
            } else {
                echo "Transaction failed with code: " . $response->code;
            }

        } catch (\Exception $e) {
            echo 'Payment verification failed: ' . $e->getMessage();
        }
    } else {
        echo 'No matching transaction found for this authority code.';
    }
} else {
    echo 'Transaction was cancelled or failed.';
}
