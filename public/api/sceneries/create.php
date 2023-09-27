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

// Connect to blob storage
$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/blobStorage.php';

if (is_array($data)) {

    try {

        forEach($data as $entry) {

            // Prepare 
            $scenery['user_id']          = $entry['user_id'];
            $scenery['user_login']       = (new User($scenery['user_id']))->login;
            $scenery['category']         = 'marker';
            $scenery['name']             = htmlspecialchars($entry['name']);
            $scenery['description']      = htmlspecialchars($entry['description']);
            $scenery['lng']              = $entry['lng'];
            $scenery['lat']              = $entry['lat'];
            $scenery['city']             = (new LngLat($entry['lng'], $entry['lat']))->queryGeolocation()->city;
            $scenery['prefecture']       = (new LngLat($entry['lng'], $entry['lat']))->queryGeolocation()->prefecture;
            $scenery['elevation']        = $entry['elevation'];
            $scenery['date']             = new DateTime();
            $scenery['date']->setTimestamp($entry['date']);
            $scenery['date']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            $scenery['month']            = date("n");
            $scenery['publication_date'] = (new DateTime(date('Y-m-d H:i:s'), new DateTimezone('Asia/Tokyo')));
            $scenery['popularity']       = 30;
            $scenery['tags']             = $entry['tags'];

            // Prepare photos
            $scenery['photos'] = [];
            foreach ($entry['photos'] as $entry_photo) {
                $photo['filename'] = setFilename('img');
                $photo['blob'] = (new TempImage($entry['name']. '_' .$photo['filename']))->treatBase64($activity_photo['blob']);
                $photo['user_id'] = $scenery['user_id'];
                $photo['date'] = $scenery['date'];
                array_push($scenery['photos'], $photo);
            }

            // Create scenery
            $scenery = new Scenery();
            $scenery->create($scenery);

            // Insert photos
            foreach ($scenery['photos'] as $photo_data) $scenery->insertPhoto($photo_data);
        }

    // If any error have been catched, response error info
    } catch (Error $e) {

        echo json_encode(['error' => $e->getMessage() .' ('. $e->getTraceAsString(). ')']);

    }

}