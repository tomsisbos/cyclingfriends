<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();
require_once $base_directory . '/includes/functions.php';

use Stoufa\GarminApi\GarminApi;


// When receiving a ping request from Garmin server
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);


foreach ($data['activityFiles'] as $activity_files) {

    $garmin = new Garmin($activity_files['userId']);
    $garmin->populateUserTokens();

    // Save all ping calls logs in a json file
    $temp_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/temp';
    if (!file_exists($temp_directory)) mkdir($temp_directory, 0777, true); // Create user directory if necessary
    $temp_url = $temp_directory. '/' .$activity_files['summaryId']. '.json';
    file_put_contents($temp_url, $json);
    
    // Retrieve a 200 response code
    http_response_code(200);

    // Prepare parameters
    
    $parsed = parse_url($activity_files['callbackURL']);
    parse_str($parsed['query'], $params);
    $id = $params['id'];
    $token = $params['token'];
    $ext = strtolower($activity_files['fileType']);

    // Retrieve corresponding activity details
    $garmin->retrieveActivityFile($id, $token, [
        'ext' => $ext,
        'garmin_activity_id' => intval($activity_files['activityId']), 
        'garmin_user_id' => $activity_files['userId'],
        'timestamp' => intval($activity_files['startTimeInSeconds'])
    ]);
}