<?php

require '../../../includes/api-head.php';

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '700');

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

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
        $routeCoordinates = new Coordinates($data['routeData']['geometry']['coordinates'], $data['routeData']['properties']['time']);
        $route_id         = $routeCoordinates->createRoute($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $tunnels, $loading_record);

        // Build activity data
        $loading_record->setStatus('pending', 'アクティビティデータ保存中...');
        $user_id          = $connected_user->id;
        $datetime         = new DateTime();
        $datetime->setTimestamp($data['checkpoints'][0]['datetime'] / 1000);
        $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $posting_date     = new DateTime();
        $posting_date->getTimestamp();
        $posting_date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        $title            = $data['title'];
        $duration         = $data['duration'];
        $duration_running = $data['duration_running'];
        $temperature_min  = $data['temperature']['min'];
        $temperature_avg  = $data['temperature']['avg'];
        $temperature_max  = $data['temperature']['max'];
        $speed_max        = $data['speed_max'];
        $altitude_max     = $data['altitude_max'];
        $slope_max        = $data['slope_max'];
        $bike_id          = $data['bike_id'];
        $privacy          = $data['privacy'];

        // Insert data in 'activities' table
        $insert_activity = $db->prepare('INSERT INTO activities(user_id, datetime, posting_date, title, duration, duration_running, temperature_min, temperature_avg, temperature_max, speed_max, altitude_max, slope_max, bike_id, privacy, route_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $insert_activity->execute(array($user_id, $datetime->format('Y-m-d H:i:s'), $posting_date->format('Y-m-d H:i:s'), $title, $duration, $duration_running, $temperature_min, $temperature_avg, $temperature_max, $speed_max, $altitude_max, $slope_max, $bike_id, $privacy, $route_id));

        // Build checkpoints data
        $loading_record->setStatus('pending', 'チェックポイントデータ保存中...');
        foreach ($data['checkpoints'] as $checkpoint) {
            $number = $checkpoint['number'];
            $name = $checkpoint['name'];
            $type = $checkpoint['type'];
            $story = $checkpoint['story'];
            $datetime = new DateTime();
            $datetime->setTimestamp($checkpoint['datetime'] / 1000);
            $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            if (isset($checkpoint['geolocation'])) {
                $city = $checkpoint['geolocation']['city'];
                $prefecture = $checkpoint['geolocation']['prefecture'];
            } else {
                $city = NULL;
                $prefecture = NULL;
            }
            $elevation = $checkpoint['elevation'];
            $distance = ceil($checkpoint['distance'] * 10) / 10;
            $temperature = $checkpoint['temperature'];
            $lng = $checkpoint['lngLat']['lng'];
            $lat = $checkpoint['lngLat']['lat'];
            if ($number == 0) $special = 'start';
            else if ($number == count($data['checkpoints']) - 1) $special = 'goal';
            else $special = NULL;
            
            // Insert checkpoints in 'activity_checkpoints' table
            $insert_checkpoints = $db->prepare('INSERT INTO activity_checkpoints(activity_id, number, name, type, story, datetime, city, prefecture, elevation, distance, temperature, lng, lat, special) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insert_checkpoints -> execute(array($activity_id, $number, $name, $type, $story, $datetime->format('Y-m-d H:i:s'), $city, $prefecture, $elevation, $distance, $temperature, $lng, $lat, $special));
        }

        $photo_number = 1;
        // For each photo

        foreach ($data['photos'] as $photo) {
            $loading_record->setStatus('pending', '写真データ保存中... (' .$photo_number. '/' .count($data['photos']). ')');

            // Get blob ready to upload
            $temp_image = new TempImage($photo['name']);
            $img_blob = $temp_image->treatBase64($photo['blob']);

            // Build photo data
            $filename = setFilename('img');
            $img_size = $photo['size'];
            $img_name = $photo['name'];
            $img_type = $photo['type'];
            $datetime = new DateTime();
            $datetime->setTimestamp($photo['datetime'] / 1000);
            $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            if ($photo['featured'] == true) $featured = 1;
            else $featured = 0;
            $lng = $photo['lng'];
            $lat = $photo['lat'];
            
            // Prepare photos data to add to an existing mkpoint if necessary
            if (isset($data['mkpointPhotos'])) {
                for ($i = 0; $i < count($data['mkpointPhotos']); $i++) {
                    if ($data['mkpointPhotos'][$i]['photo_name'] == $img_name) {
                        $data['mkpointPhotos'][$i]['blob'] = $img_blob;
                        $data['mkpointPhotos'][$i]['size'] = $img_size;
                        $data['mkpointPhotos'][$i]['type'] = $img_type;
                    }
                }
            }

            // Insert data in 'activity_photos' table
            $insert_photos = $db->prepare('INSERT INTO activity_photos(activity_id, user_id, datetime, featured, lng, lat, filename) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $insert_photos->execute(array($activity_id, $user_id, $datetime->format('Y-m-d H:i:s'), $featured, $lng, $lat, $filename));

            // Connect to blob storage
            $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
            require $folder . '/actions/blobStorageAction.php';
            // Send file to blob storage
            $containername = 'activity-photos';
            $blobClient->createBlockBlob($containername, $filename, $img_blob);
            // Set file metadata
            $metadata = [
                'file_name' => $img_name,
                'file_type' => $img_type,
                'file_size' => $img_size,
                'activity_id' => $activity_id,
                'author_id' => $user_id,
                'date' => $datetime->format('Y-m-d H:i:s'),
                'lng' => $lng,
                'lat' => $lat
            ];
            $blobClient->setBlobMetadata($containername, $filename, $metadata);

            $photo_number++;
        }

        // Create new mkpoints if necessary
        $loading_record->setStatus('pending', '新規絶景スポット作成中...');
        if (isset($data['mkpointsToCreate'])) {

            forEach($data['mkpointsToCreate'] as $entry) {

                // Prepare variables
                $mkpoint['user_id']          = $_SESSION['id'];
                $mkpoint['user_login']       = $_SESSION['login'];
                $mkpoint['category']         = 'marker';
                $mkpoint['name']             = htmlspecialchars($entry['name']);
                $mkpoint['city']             = $entry['city'];
                $mkpoint['prefecture']       = $entry['prefecture'];
                $mkpoint['elevation']        = $entry['elevation'];
                $mkpoint['date']             = new DateTime();
                $mkpoint['date']->setTimestamp($entry['date'] / 1000);
                $mkpoint['date']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
                $mkpoint['month']            = date("n");
                $mkpoint['description']      = htmlspecialchars($entry['description']);
                $mkpoint['lng']              = $entry['lngLat']['lng'];
                $mkpoint['lat']              = $entry['lngLat']['lat'];
                $mkpoint['publication_date'] = date('Y-m-d H:i:s');
                $mkpoint['popularity']       = 30;

                // Insert mkpoint data
                $insertMkpointData = $db->prepare('INSERT INTO map_mkpoint (user_id, user_login, category, name, city, prefecture, elevation, date, month, description, lng, lat, publication_date, popularity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $insertMkpointData->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date']->format('Y-m-d H:i:s'), $mkpoint['month'], $mkpoint['description'], $mkpoint['lng'], $mkpoint['lat'], $mkpoint['publication_date'], $mkpoint['popularity']));
                // Get mkpoint id
                $getMkpointId = $db->prepare('SELECT id FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
                $getMkpointId->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
                $mkpoint['id'] = $getMkpointId->fetch()['id'];

                // Get first photo blob
                $thumbnail_set = false;
                foreach ($data['photos'] as $activity_photo) {
                    foreach ($entry['photos'] as $mkpoint_photo) {
                        if ($mkpoint_photo['name'] == $activity_photo['name']) {

                            // Get blob ready to upload
                            $temp_image = new TempImage($entry['name']);
                            $mkpoint_photo['filename'] = setFilename('img');
                            $mkpoint_photo['blob'] = $temp_image->treatBase64($activity_photo['blob']);

                            // Build and append mkpoint thumbnail
                            if (!$thumbnail_set) {
                                
                                // Build thumbnail
                                $mkpoint['thumbnail'] = $temp_image->getThumbnail();
            
                                // Insert mkpoint data
                                $insertMkpointThumbnail = $db->prepare('UPDATE map_mkpoint SET thumbnail = ? WHERE id = ?');
                                $insertMkpointThumbnail->execute(array($mkpoint['thumbnail'], $mkpoint['id']));
                                $thumbnail_set = true;
                            }

                            // Remove temp files
                            unlink($temp); unlink($path);

                            // Insert photos data
                            $insertPhotos = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
                            $insertPhotos->execute(array($mkpoint['id'], $mkpoint['user_id'], $mkpoint['date']->format('Y-m-d H:i:s'), 0, $mkpoint_photo['filename']));

                            // Send file to blob storage
                            $containername = 'scenery-photos';
                            $blobClient->createBlockBlob($containername, $mkpoint_photo['filename'], $mkpoint_photo['blob']);
                            // Set file metadata
                            $metadata = [
                                'file_name' => $mkpoint_photo['name'],
                                'file_type' => $mkpoint_photo['type'],
                                'file_size' => $mkpoint_photo['size'],
                                'scenery_id' => $mkpoint['id'],
                                'author_id' => $mkpoint['user_id'],
                                'date' => $mkpoint['publication_date'],
                                'lat' => $mkpoint['lat'],
                                'lng' => $mkpoint['lng']
                            ];
                            $blobClient->setBlobMetadata($containername, $mkpoint_photo['filename'], $metadata);
                        }
                    }

                }

                // Insert tags data
                if (!empty($entry['tags'][0])) {
                    foreach ($entry['tags'] as $tag) {
                        $insertTag = $db->prepare('INSERT INTO tags (object_type, object_id, tag) VALUES (?, ?, ?)');
                        $insertTag->execute(array('scenery', $mkpoint['id'], $tag));
                    }
                }
            }
        }

        // Update user's cleared mkpoints
        $loading_record->setStatus('pending', '絶景スポット訪問歴更新中...');
        foreach ($data['mkpoints'] as $mkpoint) {
            $addMkpoint = $db->prepare('INSERT INTO user_mkpoints(user_id, mkpoint_id, activity_id) VALUES (?, ?, ?)');
            $addMkpoint->execute(array($connected_user->id, $mkpoint['id'], $activity_id));
        }

        // Update user's cleared segments
        $loading_record->setStatus('pending', 'セグメント走行歴更新中...');
        foreach ($data['segments'] as $segment) {
            $addSegment = $db->prepare('INSERT INTO user_segments(user_id, segment_id, activity_id) VALUES (?, ?, ?)');
            $addSegment->execute(array($connected_user->id, $segment['id'], $activity_id));
        }

        // If necessary, add selected photos to corresponding mkpoint
        $loading_record->setStatus('pending', '絶景スポットへ写真追加中...');
        if (isset($data['mkpointPhotos'])) {
            foreach ($data['mkpointPhotos'] as $entry) {
                
                // Insert table data
                $entry['filename'] = setFilename('img');
                $insertImgMkpoint = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
                $insertImgMkpoint->execute(array($entry['mkpoint_id'], $_SESSION['id'], $datetime->format('Y-m-d H:i:s'), 0, $entry['filename']));

                // Get scenery lngLat data
                $current_mkpoint = new Mkpoint($entry['mkpoint_id']);
                $entry['lng'] = $current_mkpoint->lngLat->lng;
                $entry['lat'] = $current_mkpoint->lngLat->lat;

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
                    'scenery_id' => $entry['mkpoint_id'],
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

    // If any error have been catched, response error info
    } catch (Error $e) {

        $loading_record->setStatus('error', $e->getMessage() .' ('. $e->getTraceAsString(). ')');
        die();

    }

}