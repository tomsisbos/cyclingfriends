<?php

require '../../../includes/api-head.php';

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '700');

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

// Connect to blob storage
$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/blobStorageAction.php';

if (is_array($data)) {

    // Get activity id
    $activity_id = getNextAutoIncrement('activities');

    // Set loading record
    $loading_record = new LoadingRecord($connected_user->id, 'activities', $activity_id);
    $loading_record->register();

    try {

        // Build route data
        $loading_record->setStatus('pending', 'ルートデータ保存中...');
        $author_id   = $connected_user->id;
        $route_id    = 'new';
        $category    = 'activity';
        $name        = $data['title'];
        $description = '';
        $distance    = $data['distance'];
        $elevation   = $data['elevation'];
        $startplace  = $data['checkpoints'][0]['geolocation'];
        $goalplace   = $data['checkpoints'][count($data['checkpoints']) - 1]['geolocation'];
        $thumbnail   = $data['thumbnail'];
        $tunnels     = [];

        // Insert data in 'routes' table
        $routeCoordinates = new CFLinestring($data['routeData']['geometry']['coordinates'], $data['routeData']['properties']['time']);
        $route_id         = $routeCoordinates->createRoute($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $tunnels, $loading_record);

        // Build activity data
        $loading_record->setStatus('pending', 'アクティビティデータ保存中...');
        $user_id          = $connected_user->id;
        $datetime         = new DateTime();
        $datetime->setTimestamp($data['checkpoints'][0]['datetime'] / 1000);
        $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $posting_date     = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
        $title            = $data['title'];
        $duration         = $data['duration'];
        $duration_running = $data['duration_running'];
        if (count($data['temperature']) == 3) {
            $temperature_min = $data['temperature']['min'];
            $temperature_avg = $data['temperature']['avg'];
            $temperature_max = $data['temperature']['max'];
        } else {
            $temperature_min = null;
            $temperature_avg = null;
            $temperature_max = null;
        }
        $speed_max        = $data['speed_max'];
        $altitude_max     = $data['altitude_max'];
        $slope_max        = $data['slope_max'];
        if (isset($data['bike_id'])) $bike_id = $data['bike_id'];
        else $bike_id = null;
        $privacy          = $data['privacy'];

        // Insert data in 'activities' table
        $insert_activity = $db->prepare('INSERT INTO activities(user_id, datetime, posting_date, title, duration, duration_running, temperature_min, temperature_avg, temperature_max, speed_max, altitude_max, slope_max, bike_id, privacy, route_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert_activity->execute(array($user_id, $datetime->format('Y-m-d H:i:s'), $posting_date->format('Y-m-d H:i:s'), $title, $duration, $duration_running, $temperature_min, $temperature_avg, $temperature_max, $speed_max, $altitude_max, $slope_max, $bike_id, $privacy, $route_id));

        // Build checkpoints data
        $loading_record->setStatus('pending', 'チェックポイントデータ保存中...');
        foreach ($data['checkpoints'] as $checkpoint) {
            $checkpoint_data['activity_id'] = $activity_id;
            $checkpoint_data['number'] = $checkpoint['number'];
            $checkpoint_data['name'] = $checkpoint['name'];
            $checkpoint_data['type'] = $checkpoint['type'];
            $checkpoint_data['story'] = $checkpoint['story'];
            $checkpoint_data['datetime'] = new DateTime();
            $checkpoint_data['datetime']->setTimestamp($checkpoint['datetime'] / 1000);
            $checkpoint_data['datetime']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            if (isset($checkpoint['geolocation'])) {
                $checkpoint_data['city'] = $checkpoint['geolocation']['city'];
                $checkpoint_data['prefecture'] = $checkpoint['geolocation']['prefecture'];
            } else {
                $checkpoint_data['city'] = NULL;
                $checkpoint_data['prefecture'] = NULL;
            }
            $checkpoint_data['elevation'] = $checkpoint['elevation'];
            $checkpoint_data['distance'] = ceil($checkpoint['distance'] * 10) / 10;
            $checkpoint_data['temperature'] = $checkpoint['temperature'];
            $checkpoint_data['lng'] = $checkpoint['lngLat']['lng'];
            $checkpoint_data['lat'] = $checkpoint['lngLat']['lat'];
            if ($checkpoint['number'] == 0) $checkpoint_data['special'] = 'start';
            else if ($checkpoint['number'] == count($data['checkpoints']) - 1) $checkpoint_data['special'] = 'goal';
            else $checkpoint_data['special'] = NULL;

            $checkpoint = new ActivityCheckpoint();
            $checkpoint->create($checkpoint_data);
        }

        $photo_number = 1;
        // For each photo

        foreach ($data['photos'] as $photo) {
            $loading_record->setStatus('pending', '写真データ保存中... (' .$photo_number. '/' .count($data['photos']). ')');

            // Get blob ready to upload
            $temp_image = new TempImage($photo['name']);
            $activity_photo_data['blob'] = $temp_image->treatBase64($photo['blob']);

            // Build photo data
            $activity_photo_data['user_id'] = $connected_user->id;
            $activity_photo_data['activity_id'] = $activity_id;
            $activity_photo_data['size'] = $photo['size'];
            $activity_photo_data['name'] = $photo['name'];
            $activity_photo_data['type'] = $photo['type'];
            $activity_photo_data['lng'] = $photo['lng'];
            $activity_photo_data['lat'] = $photo['lat'];
            $activity_photo_data['datetime'] = new DateTime();
            $activity_photo_data['datetime']->setTimestamp($photo['datetime'] / 1000);
            $activity_photo_data['datetime']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            if ($photo['featured'] == true) $activity_photo_data['featured'] = 1;
            else $activity_photo_data['featured'] = 0;
            $activity_photo_data['privacy'] = $photo['privacy'];

            $activity_photo = new ActivityPhoto();
            $activity_photo->create($activity_photo_data);
            
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

            $photo_number++;
        }

        // Create new sceneries if necessary
        $loading_record->setStatus('pending', '新規絶景スポット作成中...');
        if (isset($data['sceneriesToCreate']) && !empty($data['sceneriesToCreate'])) {

            forEach($data['sceneriesToCreate'] as $entry) {
                
                // Get photo blobs and thumbnail ready
                $thumbnail_set = false;
                $thumbnail = null;
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
                            
                            // Build and append scenery thumbnail (from first photo blob)
                            if (!$thumbnail_set) {
                                $thumbnail = $temp_image->getThumbnail();
                                $thumbnail_set = true;
                            }
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
                $scenery_data['date']->setTimestamp($entry['date'] / 1000);
                $scenery_data['date']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
                $scenery_data['month']            = date("n");
                $scenery_data['description']      = htmlspecialchars($entry['description']);
                $scenery_data['thumbnail']        = $thumbnail;
                $scenery_data['lng']              = $entry['lngLat']['lng'];
                $scenery_data['lat']              = $entry['lngLat']['lat'];
                $scenery_data['publication_date'] = date('Y-m-d H:i:s');
                $scenery_data['popularity']       = 30;
                $scenery_data['tags']             = $entry['tags'];

                // Create scenery
                $scenery = new Scenery();
                $scenery->create($scenery_data);
            }
        }

        // If necessary, add selected photos to corresponding scenery
        $loading_record->setStatus('pending', '絶景スポットへ写真追加中...');
        if (isset($data['sceneryPhotos']) && !empty($data['sceneryPhotos'])) {
            foreach ($data['sceneryPhotos'] as $entry) {
                
                // Insert table data
                $entry['filename'] = setFilename('img');
                $insertImgScenery = $db->prepare('INSERT INTO scenery_photos (scenery_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
                $insertImgScenery->execute(array($entry['scenery_id'], $_SESSION['id'], $datetime->format('Y-m-d H:i:s'), 0, $entry['filename']));

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
                    'author_id' => $_SESSION['id'],
                    'date' => $datetime->format('Y-m-d H:i:s'),
                    'lat' => $entry['lat'],
                    'lng' => $entry['lng']
                ];
                $blobClient->setBlobMetadata($containername, $entry['filename'], $metadata);
            }
        }
        
        $loading_record->setStatus('success', '「' .$title. '」が無事に保存されました。詳細を<a href="/activity/' .$activity_id. '">こちら</a>でご確認ただけます。');
        echo json_encode(true);
        die();

    // If any error have been catched, response error info
    } catch (Error $e) {

        $loading_record->setStatus('error', $e->getMessage() .' ('. $e->getTraceAsString(). ')');
        die();

    }

}