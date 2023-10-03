<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '700');

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    try {

        // Prepare 
        $scenery_data['user_id']          = $data['user_id'];
        $scenery_data['user_login']       = (new User($scenery_data['user_id']))->login;
        $scenery_data['category']         = 'marker';
        $scenery_data['name']             = htmlspecialchars($data['name']);
        $scenery_data['description']      = htmlspecialchars($data['description']);
        $scenery_data['lng']              = $data['lng'];
        $scenery_data['lat']              = $data['lat'];
        $scenery_data['city']             = (new LngLat($data['lng'], $data['lat']))->queryGeolocation()->city;
        $scenery_data['prefecture']       = (new LngLat($data['lng'], $data['lat']))->queryGeolocation()->prefecture;
        $scenery_data['elevation']        = $data['elevation'];
        $scenery_data['date']             = (new DateTime(date('Y-m-d H:i:s'), new DateTimezone('Asia/Tokyo')));
        $scenery_data['month']            = date("n");
        $scenery_data['publication_date'] = $scenery_data['date'];
        $scenery_data['popularity']       = 30;
        $scenery_data['tags']             = $data['tags'];

        // Prepare photos
        $scenery_data['photos'] = [];
        foreach ($data['scenery_photos'] as $photo) {
            $photo['filename'] = setFilename('img');
            $photo['user_id'] = $scenery_data['user_id'];
            $photo['date'] = $scenery_data['date'];

            // Prepare blob for upload to blob storage
            $ext = strtolower(substr($photo['name'], -3));
            $img_name = 'temp_' .$photo['filename']. '.'.$ext;
            $temp = $_SERVER["DOCUMENT_ROOT"]. '/media/activities/temp/' .$img_name; // Set temp path
            // Temporary upload raw file on the server
            base64_to_jpeg($photo['blob'], $temp);
            $photo['blob'] = fopen($temp, 'r');

            array_push($scenery_data['photos'], $photo);
        }

        // Create scenery
        $scenery = new Scenery();
        $scenery->create($scenery_data);

        echo json_encode(['success' => $scenery->id]);

    // If any error have been catched, response error info
    } catch (Error $e) {

        echo json_encode(['error' => $e->getMessage() .' ('. $e->getTraceAsString(). ')']);

    }

}