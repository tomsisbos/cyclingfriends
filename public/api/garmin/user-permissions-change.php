<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();


// When receiving a push request from Garmin server
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

// Save all ping calls logs in a json file
$temp_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/temp';
if (!file_exists($temp_directory)) mkdir($temp_directory, 0777, true); // Create user directory if necessary
$temp_url = $temp_directory. '/permission-change.json';
file_put_contents($temp_url, $json);

$garmin = new Garmin($data['userPermissionsChange']['userId']);
$garmin->populateUserTokens();
if (in_array('ACTIVITY_EXPORT', $garmin->permissions)) $garmin->setPermission('activity', true);
else $garmin->setPermission('activity', false);
if (in_array('COURSE_IMPORT', $garmin->permissions)) $garmin->setPermission('course', true);
else $garmin->setPermission('course', false);

// Retrieve a 200 response code
http_response_code(200);