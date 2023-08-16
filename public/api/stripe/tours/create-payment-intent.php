<?php


require_once '../../../../includes/api-head.php';

$stripeSecretKey = getEnv('STRIPE_SECRET_KEY');

\Stripe\Stripe::setApiKey($stripeSecretKey);
\Stripe\Stripe::setApiVersion('2022-11-15');

// Stripe customer management
if (getConnectedUser()->getCustomerId()) {
    $customer = \Stripe\Customer::retrieve(getConnectedUser()->getCustomerId(), []);

} else {
    $customer = \Stripe\Customer::create([
        'email' => getConnectedUser()->email,
        'name' => getConnectedUser()->login,
        'description' => getConnectedUser()->lastname. ' ' .getConnectedUser()->firstname
    ]);
    getConnectedUser()->setCustomerId($customer->id);
}

header('Content-Type: application/json');

try {
    
    // Retrieve ride id from POST body
    $json = file_get_contents('php://input');
    $ride_id = json_decode($json);
    $ride = new Ride($ride_id);

    // Calculate amount
    $amount = $ride->calculateAmount(getConnectedUser()->id);

    // Retrieve entry data from session and populate necessary metadata to perform after payment traitment
    $entry_data = $_SESSION['ride_entry_data_' .$ride->id];
    $metadata = [
        'ride_id' => $ride_id,
        'user_id' => getConnectedUser()->id
    ];

    //
    foreach ($entry_data as $key => $value) $metadata[$key] = $value;
    if (count($amount->discounts) > 0) {
        $use_cf_points = 0;
        foreach ($amount->discounts as $discount) {
            if ($discount->name == 'ポイント利用分') $use_cf_points += abs($discount->price);
        }
        if ($use_cf_points > 0) $metadata['use_cf_points'] = $use_cf_points;
    }

    // Create a PaymentIntent with amount and currency
    $paymentIntent = \Stripe\PaymentIntent::create([
        'customer' => $customer->id,
        'setup_future_usage' => 'off_session',
        'amount' => $amount->total,
        'currency' => 'jpy',
        'automatic_payment_methods' => [
            'enabled' => true,
        ],
        'metadata' => $metadata
    ]);

    $output = [
        'clientSecret' => $paymentIntent->client_secret,
    ];

    echo json_encode($output);
} catch (Error $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}