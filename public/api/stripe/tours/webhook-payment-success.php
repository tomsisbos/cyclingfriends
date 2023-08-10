<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();

$stripeSecretKey = getEnv('STRIPE_SECRET_KEY_TEST');

\Stripe\Stripe::setApiKey($stripeSecretKey);

$endpoint_secret = 'whsec_t8rwAo8dFjsV1XHdSW1sDCnYpNi9bYwK';

$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(
        json_decode($payload, true)
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    echo '⚠️  Webhook error while parsing basic request.';
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
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        echo '⚠️  Webhook error while validating signature.';
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
    case 'payment_method.attached':
        $paymentMethod = $event->data->object; // contains a \Stripe\PaymentMethod
        // Then define and call a method to handle the successful attachment of a PaymentMethod.
        // handlePaymentMethodAttached($paymentMethod);
        break;
    default:
        // Unexpected event type
        error_log('Received unknown event type');
}

http_response_code(200);

function handleSuccessfulPayment ($paymentIntent) {

    if (isset($_SESSION['ride_entry_data_' .$ride->id])) {

        $entry_data = $_SESSION['ride_entry_data_' .$ride->id];

        // Update user information
        foreach ($entry_data as $key => $value) if (substr($key, 0, 8) !== 'a_field_') getConnectedUser()->update($key, $value);
        foreach ($ride->getAdditionalFields() as $a_field) $a_field->setAnswer(getConnectedUser()->id, $entry_data['a_field_' .$a_field->id. '_type'], $entry_data['a_field_' .$a_field->id]);

        // Join ride
        $ride->join(getConnectedUser());

        // Clear session variable
        unset($_SESSION['ride_entry_data_' .$ride->id]);

    } else header('location:' .$router->generate('ride-participations'));

}