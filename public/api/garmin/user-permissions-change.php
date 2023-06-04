<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();


// When receiving a push request from Garmin server
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

$garmin = new Garmin($data['userId']);
$garmin->populateUserTokens();
if (in_array('ACTIVITY_EXPORT', $permissions)) $garmin->setPermission('activity', true);
else $garmin->setPermission('activity', false);
if (in_array('COURSE_IMPORT', $permissions)) $garmin->setPermission('course', true);
else $garmin->setPermission('course', false);

// Retrieve a 200 response code
http_response_code(200);