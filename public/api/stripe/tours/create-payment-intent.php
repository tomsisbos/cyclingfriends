<?php


require_once '../../../../includes/api-head.php';

$stripeSecretKey = getEnv('STRIPE_SECRET_KEY_TEST');

\Stripe\Stripe::setApiKey($stripeSecretKey);

header('Content-Type: application/json');

try {
    // retrieve JSON from POST body
    $json = file_get_contents('php://input');
    $ride_id = json_decode($json);
    $ride = new Ride($ride_id);

    // Create a PaymentIntent with amount and currency
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $ride->calculateAmount(getConnectedUser()->id)->total,
        'currency' => 'jpy',
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
    ]);

    $output = [
        'clientSecret' => $paymentIntent->client_secret,
    ];

    echo json_encode($output);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}