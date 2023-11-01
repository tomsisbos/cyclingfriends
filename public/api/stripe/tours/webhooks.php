<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();

$stripeSecretKey = getEnv('STRIPE_SECRET_KEY');
///$stripeSecretKey = getEnv('STRIPE_SECRET_KEY_TEST'); ///TEST MODE

\Stripe\Stripe::setApiKey($stripeSecretKey);
\Stripe\Stripe::setApiVersion('2022-11-15');

$endpoint_secret = 'whsec_v9aNHTb7pS1dtoVf9XOJToD6UyOcToCm';
///$endpoint_secret = 'whsec_t8rwAo8dFjsV1XHdSW1sDCnYpNi9bYwK'; ///TEST MODE

$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(
        json_decode($payload, true)
    );
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    echo '⚠️ Webhook error while parsing basic request.';
    http_response_code(400);
    exit();
}

if ($endpoint_secret) {
    // Only verify the event if there is an endpoint secret defined
    // Otherwise use the basic decoded event
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    try {
        $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
        );
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        echo '⚠️ Webhook error while validating signature.';
        http_response_code(400);
        exit();
    }
}

// Handle the event
switch ($event->type) {
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object; 
        handleSuccessfulPayment($paymentIntent);
        break;
    case 'payment_intent.payment_failed':
        $paymentIntent = $event->data->object; 
        handleFailedPayment($paymentIntent);
        break;
    case 'payment_method.attached':
        $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
        // Then define and call a method to handle the successful attachment of a PaymentMethod.
        // handlePaymentMethodAttached($paymentMethod);
        break;
    default:
        // Unexpected event type
        error_log('Received unknown event type : ' .$event->type);
}

http_response_code(200);

function handleSuccessfulPayment ($paymentIntent) {

    // Retrieve data from paymentIntent metadata
    $metadata = json_decode(json_encode($paymentIntent->metadata), true);
    $user_data = []; $additional_fields = [];

    foreach ($metadata as $key => $value) {
        if ($key == 'ride_id') $ride = new Ride($value);
        else if ($key == 'user_id') $user = new User($value);
        else if (substr($key, 0, 8) === 'a_field_') $additional_fields[$key] = $value;
        else if ($key == 'use_cf_points') (new User($metadata['user_id']))->removeCFPoints($value);
        else if ($key == 'rental_bike') $ride->finalizeRentalBikeEntry($user->id);
        else $user_data[$key] = $value;
    }

    // Update user information
    foreach ($user_data as $key => $value) $user->update($key, $value);
    foreach ($ride->getAdditionalFields() as $a_field) $a_field->setAnswer($user->id, $additional_fields['a_field_' .$a_field->id. '_type'], $additional_fields['a_field_' .$a_field->id]);

    // Join ride
    $ride->join($user);

}

function handleFailedPayment ($paymentIntent) {

    return;

}