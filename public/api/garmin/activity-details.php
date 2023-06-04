<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();

use Stoufa\GarminApi\GarminApi;


// When receiving a ping request from Garmin server
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);


foreach ($data['activityDetails'] as $activity_details) {

    $garmin = new Garmin($activity_details['userId']);
    $garmin->populateUserTokens();

    // Save all ping calls logs in a json file
    $temp_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/temp';
    if (!file_exists($temp_directory)) mkdir($temp_directory, 0777, true); // Create user directory if necessary
    $temp_url = $temp_directory. '/' .$activity_details['summaryId']. '.json';
    file_put_contents($temp_url, $json);
    
    // Retrieve a 200 response code
    http_response_code(200);

    // Retrieve corresponding activity details
    $garmin->retrieveActivityDetails($activity_details['uploadStartTimeInSeconds'], $activity_details['uploadEndTimeInSeconds']);
}