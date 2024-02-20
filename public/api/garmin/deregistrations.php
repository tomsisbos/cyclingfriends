<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();


// When receiving a push request from Garmin server
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

foreach ($data['deregistrations'] as $deregistration) {
    $garmin = new Garmin($deregistration['userId']);
    $garmin->populateUserTokens();
    $garmin->removeUserEntry();
}

// Retrieve a 200 response code
http_response_code(200);