<?php

require '../../../includes/api-head.php';

ini_set('memory_limit', '1024M');
ini_set('max_execution_time', '700');

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    try {

        // Build route data
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
        $route_id         = $routeCoordinates->createRoute($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $tunnels);

        // Build activity data
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

        // Get activity id
        $get_activity_id = $db->prepare('SELECT id FROM activities WHERE user_id = ? AND route_id = ?');
        $get_activity_id->execute(array($user_id, $route_id));
        $activity_id = $get_activity_id->fetch()['id'];

        // Build checkpoints data
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
        

        // For each photo
        foreach ($data['photos'] as $photo) {

            // Photo treatment
            $ext = strtolower(substr($photo['name'], -3));
            $img_name = 'temp.'.$ext;
            $temp = $_SERVER["DOCUMENT_ROOT"]. '/media/activities/temp/' .$img_name; // Set temp path
            // Temporary upload raw file on the server
            base64_to_jpeg($photo['blob'], $temp);
            // Get the file into $img thanks to imagecreatefromjpeg
            $img = imagecreatefromjpegexif($temp);
            if (imagesx($img) > 1600) $img = imagescale($img, 1600); // Only scale if img is wider than 1600px
            // Correct image gamma and contrast
            imagegammacorrect($img, 1.0, 1.1);
            imagefilter($img, IMG_FILTER_CONTRAST, -5);
            // Compress it and move it into a new folder
            $path = $_SERVER["DOCUMENT_ROOT"]. "/media/activities/temp/photo_" .$img_name; // Set path variable
            imagejpeg($img, $path, 75); // Set new quality to 75
            
            // Set filename for blob server
            $filename = 'img_' . rand(0, 999999999999) . '.jpg';

            // Build photo data
            $img_blob = fopen($path, 'r');
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
            $insert_photos = $db->prepare('INSERT INTO activity_photos(activity_id, user_id, datetime, featured, filename) VALUES (?, ?, ?, ?, ?)');
            $insert_photos->execute(array($activity_id, $user_id, $datetime->format('Y-m-d H:i:s'), $featured, $filename));

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
        }

        // Create new mkpoints if necessary
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
                $mkpoint['date']             = date('Y-m-d H:i:s');
                $mkpoint['month']            = date("n");
                $mkpoint['description']      = htmlspecialchars($entry['description']);
                $mkpoint['lng']              = $entry['lngLat']['lng'];
                $mkpoint['lat']              = $entry['lngLat']['lat'];
                $mkpoint['publication_date'] = date('Y-m-d H:i:s');
                $mkpoint['popularity']       = 30;

                // Insert mkpoint data
                $insertMkpointData = $db->prepare('INSERT INTO map_mkpoint (user_id, user_login, category, name, city, prefecture, elevation, date, month, description, lng, lat, publication_date, popularity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $insertMkpointData->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date'], $mkpoint['month'], $mkpoint['description'], $mkpoint['lng'], $mkpoint['lat'], $mkpoint['publication_date'], $mkpoint['popularity']));
                // Get mkpoint id
                $getMkpointId = $db->prepare('SELECT id FROM map_mkpoint WHERE ROUND(lng, 3) = ? AND ROUND(lat, 3) = ?');
                $getMkpointId->execute(array(round($mkpoint['lng'], 3), round($mkpoint['lat'], 3)));
                $mkpoint['id'] = $getMkpointId->fetch()['id'];

                // Get first photo blob
                $thumbnail_set = false;
                foreach ($data['photos'] as $activity_photo) {
                    foreach ($entry['photos'] as $mkpoint_photo) {
                        if ($mkpoint_photo['name'] == $activity_photo['name']) {
                            // Prepare blob
                            $ext = strtolower(substr($entry['name'], -3));
                            $img_name = 'temp.'.$ext;
                            $temp = $_SERVER["DOCUMENT_ROOT"]. '/media/activities/temp/' .$img_name; // Set temp path
                            // Temporary upload raw file on the server
                            base64_to_jpeg($activity_photo['blob'], $temp);
                            // Get the file into $img thanks to imagecreatefromjpeg
                            $img = imagecreatefromjpegexif($temp);
                            if (imagesx($img) > 1600) $img = imagescale($img, 1600); // Only scale if img is wider than 1600px
                            // Correct image gamma and contrast
                            imagegammacorrect($img, 1.0, 1.1);
                            imagefilter($img, IMG_FILTER_CONTRAST, -5);
                            // Compress it and move it into a new folder
                            $path = $_SERVER["DOCUMENT_ROOT"]. "/media/activities/temp/photo_" .$img_name; // Set path variable
                            imagejpeg($img, $path, 75); // Set new quality to 75

                            // Build and append mkpoint thumbnail
                            if (!$thumbnail_set) {
                                // Get image and scale it to thumbnail size
                                $thumbnail = imagecreatefromjpegexif($path);
                                $thumbnail = imagescale($thumbnail, 48, 36);
                                // Correct image gamma and contrast
                                imagegammacorrect($thumbnail, 1.0, 1.275);
                                imagefilter($thumbnail, IMG_FILTER_CONTRAST, -12);
                                $thumbpath = $_SERVER["DOCUMENT_ROOT"]. '/map/media/temp/thumb_' .$img_name; // Set path variable
                                imagejpeg($thumbnail, $thumbpath);
                                // Insert mkpoint data
                                $mkpoint['thumbnail'] = base64_encode(file_get_contents($thumbpath));
                                $insertMkpointThumbnail = $db->prepare('UPDATE map_mkpoint SET thumbnail = ? WHERE id = ?');
                                $insertMkpointThumbnail->execute(array($mkpoint['thumbnail'], $mkpoint['id']));
                                $thumbnail_set = true;
                                unlink($thumbpath);
                            }

                            // Set blob and filename for blob server
                            $mkpoint_photo['filename'] = 'img_' . rand(0, 999999999999) . '.jpg';
                            $mkpoint_photo['blob'] = fopen($path, 'r');

                            // Remove temp files
                            unlink($temp); unlink($path);

                            // Insert photos data
                            $insertPhotos = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
                            $insertPhotos->execute(array($mkpoint['id'], $mkpoint['user_id'], $mkpoint['date'], 0, $mkpoint_photo['filename']));

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
        foreach ($data['mkpoints'] as $mkpoint) {
            $addMkpoint = $db->prepare('INSERT INTO user_mkpoints(user_id, mkpoint_id, activity_id) VALUES (?, ?, ?)');
            $addMkpoint->execute(array($connected_user->id, $mkpoint['id'], $activity_id));
        }

        // Update user's cleared segments
        foreach ($data['segments'] as $segment) {
            $addSegment = $db->prepare('INSERT INTO user_segments(user_id, segment_id, activity_id) VALUES (?, ?, ?)');
            $addSegment->execute(array($connected_user->id, $segment['id'], $activity_id));
        }

        // If necessary, add selected photos to corresponding mkpoint
        if (isset($data['mkpointPhotos'])) {
            foreach ($data['mkpointPhotos'] as $entry) {
                
                // Insert table data
                $entry['filename'] = 'img_' . rand(0, 999999999999) . '.jpg';
                $insertImgMkpoint = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, date, likes, filename) VALUES (?, ?, ?, ?, ?)');
                $insertImgMkpoint->execute(array($entry['mkpoint_id'], $_SESSION['id'], date('Y-m-d H:i:s'), 0, $entry['filename']));

                // Get scenery lngLat data
                $current_mkpoint = new Mkpoint($entry['mkpoint_id']);
                $entry['lng'] = $current_mkpoint->lngLat->lng;
                $entry['lat'] = $current_mkpoint->lngLat->lat;

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
                    'date' => date('Y-m-d H:i:s'),
                    'lat' => $entry['lat'],
                    'lng' => $entry['lng']
                ];
                $blobClient->setBlobMetadata($containername, $mkpoint_photo['filename'], $metadata);
            }
        }
            
        echo json_encode(true);

    // If any exception have been catched, response the error message set in the exception
    } catch(Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
        die();
    }

}