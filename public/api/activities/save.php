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

        if (getConnectedUser()) $author_id = getConnectedUser()->id;
        else $author_id = $data['author_id'];

        // Prepare data structure
        $summary = $data['activityData']['summary'];
        $coordinates = [];
        $trackpoints = [];
        for ($i = 0; $i < count($data['activityData']['linestring']['coordinates']); $i++) {
            $coord = $data['activityData']['linestring']['coordinates'][$i];
            $tpoint = $data['activityData']['linestring']['trackpoints'][$i];
            array_push($coordinates, new LngLat($coord['lng'], $coord['lat']));
            array_push($trackpoints, new Trackpoint($tpoint));
        }
        $summary['startplace'] = new Geolocation($summary['startplace']['city'], $summary['startplace']['prefecture']);
        $summary['goalplace'] = new Geolocation($summary['goalplace']['city'], $summary['goalplace']['prefecture']);
        $summary['duration'] = new DateInterval('PT' .$summary['duration']['h']. 'H' .$summary['duration']['i']. 'M' .$summary['duration']['s']. 'S');
        $summary['duration_running'] = new DateInterval('PT' .$summary['duration_running']['h']. 'H' .$summary['duration_running']['i']. 'M' .$summary['duration_running']['s']. 'S');
        if (!isset($summary['title'])) $summary['title'] = $data['activityData']['title'];
    
        // Prepare activity data
        $activity_data = new ActivityData($summary, $coordinates, $trackpoints);
        $editable_data = [
            'title' => $data['title'],
            'privacy' => $data['privacy'],
            'bike_id' => $data['bike_id'],
            'checkpoints' => $data['checkpoints']
        ];
        $activity_id = $activity_data->createActivity($author_id, $editable_data);

        $activity = new Activity($activity_id);
        
        // Add photos
        foreach ($data['photos'] as $photo) {

            include $base_directory . '/actions/activities/photoTreatment.php';
            
            // Prepare photos data to add to an existing scenery if necessary
            if (isset($data['sceneryPhotos'])) {
                for ($i = 0; $i < count($data['sceneryPhotos']); $i++) {
                    if ($data['sceneryPhotos'][$i]['photo_name'] == $photo['name']) {
                        $data['sceneryPhotos'][$i]['blob'] = $photo['blob'];
                        $data['sceneryPhotos'][$i]['size'] = $photo['size'];
                        $data['sceneryPhotos'][$i]['type'] = $photo['type'];
                    }
                }
            }
        }

        // Create new sceneries if necessary
        if (isset($data['sceneriesToCreate']) && !empty($data['sceneriesToCreate'])) {

            forEach($data['sceneriesToCreate'] as $entry) {
                
                // Get photo blobs ready
                $scenery_data['photos'] = [];
                foreach ($data['photos'] as $activity_photo) {
                    foreach ($entry['photos'] as $entry_photo) {
                        if ($entry_photo['name'] == $activity_photo['name']) {

                            // Get blob ready to upload
                            $scenery_photo = $entry_photo;
                            $scenery_photo['filename'] = setFilename('img');
                            $temp_image = new TempImage($entry['name']. '_' .$scenery_photo['filename']);
                            $scenery_photo['blob'] = $temp_image->treatBase64($activity_photo['blob']);

                            // Add photo data to scenery photos
                            array_push($scenery_data['photos'], $scenery_photo);
                        }
                    }
                }

                // Prepare variables
                $scenery_data['user_id']          = $_SESSION['id'];
                $scenery_data['user_login']       = $_SESSION['login'];
                $scenery_data['category']         = 'marker';
                $scenery_data['name']             = htmlspecialchars($entry['name']);
                $scenery_data['city']             = $entry['city'];
                $scenery_data['prefecture']       = $entry['prefecture'];
                $scenery_data['elevation']        = $entry['elevation'];
                $scenery_data['date']             = new DateTime();
                $scenery_data['date']->setTimestamp($entry['date']);
                $scenery_data['date']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
                $scenery_data['month']            = date("n");
                $scenery_data['description']      = htmlspecialchars($entry['description']);
                $scenery_data['lng']              = $entry['lngLat']['lng'];
                $scenery_data['lat']              = $entry['lngLat']['lat'];
                $scenery_data['publication_date'] = (new DateTime(date('Y-m-d H:i:s'), new DateTimezone('Asia/Tokyo')));
                $scenery_data['popularity']       = 30;
                $scenery_data['tags']             = $entry['tags'];

                // Create scenery
                $scenery = new Scenery();
                $scenery->create($scenery_data);
            }
        }

        // If necessary, add selected photos to corresponding scenery
        if (isset($data['sceneryPhotos']) && !empty($data['sceneryPhotos'])) {
            foreach ($data['sceneryPhotos'] as $entry) {
                
                // Insert table data
                $entry['filename'] = setFilename('img');
                $insertImgScenery = $db->prepare('INSERT INTO scenery_photos (scenery_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
                $insertImgScenery->execute(array($entry['scenery_id'], $_SESSION['id'], $summary['start_time']['date'], 0, $entry['filename']));

                // Get scenery lngLat data
                $current_scenery = new Scenery($entry['scenery_id']);
                $entry['lng'] = $current_scenery->lngLat->lng;
                $entry['lat'] = $current_scenery->lngLat->lat;

                // Get corresponding blob
                foreach ($data['photos'] as $photo) {
                    if ($photo['name'] == $entry['photo_name']) $base64 = $photo['blob'];
                }

                // Prepare blob for upload to blob storage
                $ext = strtolower(substr($entry['photo_name'], -3));
                $img_name = 'temp.'.$ext;
                $temp = $_SERVER["DOCUMENT_ROOT"]. '/media/activities/temp/' .$img_name; // Set temp path
                // Temporary upload raw file on the server
                base64_to_jpeg($base64, $temp);
                $entry['blob'] = fopen($temp, 'r');

                // Send file to blob storage
                $containername = 'scenery-photos';
                $blobClient->createBlockBlob($containername, $entry['filename'], $entry['blob']);
                // Set file metadata
                $metadata = [
                    'file_name' => $entry['photo_name'],
                    'file_type' => $entry['type'],
                    'file_size' => $entry['size'],
                    'scenery_id' => $entry['scenery_id'],
                    'author_id' => $author_id,
                    'date' => $summary['start_time']['date'],
                    'lat' => $entry['lat'],
                    'lng' => $entry['lng']
                ];
                $blobClient->setBlobMetadata($containername, $entry['filename'], $metadata);
            }
        }
        
        echo json_encode(['success' => $activity_id]);

    // If any error have been catched, response error info
    } catch (Error $e) {

        echo json_encode(['error' => $e->getMessage() .' ('. $e->getTraceAsString(). ')']);

    }

}