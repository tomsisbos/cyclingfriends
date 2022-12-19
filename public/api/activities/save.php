<?php

require '../../../includes/api-head.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

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

        // Build photo data
        $img_blob = base64_encode(file_get_contents($path));
        $img_size = $photo['size'];
        $img_name = $photo['name'];
        $img_type = $photo['type'];
        $datetime = new DateTime();
        $datetime->setTimestamp($photo['datetime'] / 1000);
        $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
        if ($photo['featured'] == true) $featured = 1;
        else $featured = 0;
        
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

        // Insert photo in 'activity_photos' table
        $insert_photos = $db->prepare('INSERT INTO activity_photos(activity_id, user_id, img_blob, img_size, img_name, img_type, datetime, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $insert_photos -> execute(array($activity_id, $user_id, $img_blob, $img_size, $img_name, $img_type, $datetime->format('Y-m-d H:i:s'), $featured));
    }

    // Create a new mkpoint if necessary
    if (isset($data['mkpointsToCreate'])) {

        // If necessary, create a new mkpoint
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
            $mkpoint['period']           = getPeriod($mkpoint['date']);
            $mkpoint['description']      = htmlspecialchars($entry['description']);
            $mkpoint['lng']              = $entry['lngLat']['lng'];
            $mkpoint['lat']              = $entry['lngLat']['lat'];
            $mkpoint['publication_date'] = date('Y-m-d H:i:s');
            $mkpoint['popularity']       = 30;

            // Insert mkpoint data
            $insertMkpointData = $db->prepare('INSERT INTO map_mkpoint (user_id, user_login, category, name, city, prefecture, elevation, date, month, period, description, lng, lat, publication_date, popularity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertMkpointData->execute(array($mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['category'], $mkpoint['name'], $mkpoint['city'], $mkpoint['prefecture'], $mkpoint['elevation'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint['description'], $mkpoint['lng'], $mkpoint['lat'], $mkpoint['publication_date'], $mkpoint['popularity']));
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

                        $mkpoint_photo['blob'] = base64_encode(file_get_contents($path));
                        unlink($temp); unlink($path);

                        // Insert photos data
                        $insertPhotos = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, user_login, date, month, period, file_blob, file_size, file_name, file_type, likes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                        $insertPhotos->execute(array($mkpoint['id'], $mkpoint['user_id'], $mkpoint['user_login'], $mkpoint['date'], $mkpoint['month'], $mkpoint['period'], $mkpoint_photo['blob'], $mkpoint_photo['size'], $mkpoint_photo['name'], $mkpoint_photo['type'], 0));
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
            $insertImgMkpoint = $db->prepare('INSERT INTO img_mkpoint (mkpoint_id, user_id, user_login, date, month, period, file_blob, file_size, file_name, file_type, likes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $insertImgMkpoint->execute(array($entry['mkpoint_id'], $_SESSION['id'], $_SESSION['login'], date('Y-m-d H:i:s'), date("n"), getPeriod(date('Y-m-d H:i:s')), $entry['blob'], $entry['size'], $entry['photo_name'], $entry['type'], 0));
        }
    }

    echo json_encode(true);

}