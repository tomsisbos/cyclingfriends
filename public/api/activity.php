<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {

        if (isset($_GET['load'])) {
            $activity = new Activity($_GET['load'], false);
            $activity->checkpoints = $activity->getCheckpoints();
            $activity->photos = $activity->getPhotos();
            echo json_encode($activity);
        }

        if (isset($_GET['delete'])) {
            $activity = new Activity($_GET['delete']);
            if ($connected_user == $activity->user) $activity->delete();
            else "You don't have necessary rights to delete this activity.";
            echo json_encode(true);
        }

    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    //var_dump($data);

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
    $routeCoordinates = new Coordinates($data['routeData']['geometry']['coordinates']);
    $route_id         = $routeCoordinates->createRoute($author_id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $thumbnail, $tunnels);

    // Build activity data
    $user_id         = $connected_user->id;
    $datetime        = new DateTime();
    $datetime->setTimestamp($data['checkpoints'][0]['datetime'] / 1000);
    $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $posting_date    = new DateTime();
    $posting_date->getTimestamp();
    $posting_date->setTimeZone(new DateTimeZone('Asia/Tokyo'));
    $title           = $data['title'];
    $duration        = $data['duration'];
    $temperature_min = $data['temperature']['min'];
    $temperature_avg = $data['temperature']['avg'];
    $temperature_max = $data['temperature']['max'];
    $bike_id         = $data['bike_id'];
    $privacy         = $data['privacy'];

    // Insert data in 'activities' table
    $insert_activity = $db->prepare('INSERT INTO activities(user_id, datetime, posting_date, title, duration, temperature_min, temperature_avg, temperature_max, bike_id, privacy, route_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert_activity->execute(array($user_id, $datetime->format('Y-m-d H:i:s'), $posting_date->format('Y-m-d H:i:s'), $title, $duration, $temperature_min, $temperature_avg, $temperature_max, $bike_id, $privacy, $route_id));

    // Get activity id
    $get_activity_id = $db->prepare('SELECT id FROM activities WHERE user_id = ? AND title = ?');
    $get_activity_id->execute(array($user_id, $title));
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
        $temp = $_SERVER["DOCUMENT_ROOT"]. '/includes/media/activities/temp/' .$img_name; // Set temp path
        // Temporary upload raw file on the server
        base64_to_jpeg($photo['blob'], $temp);
        // Get the file into $img thanks to imagecreatefromjpeg
        $img = imagecreatefromjpegexif($temp);
        if (imagesx($img) > 1600) $img = imagescale($img, 1600); // Only scale if img is wider than 1600px
        // Correct image gamma and contrast
        imagegammacorrect($img, 1.0, 1.1);
        imagefilter($img, IMG_FILTER_CONTRAST, -5);
        // Compress it and move it into a new folder
        $path = $_SERVER["DOCUMENT_ROOT"]. "/includes/media/activities/temp/photo_" .$img_name; // Set path variable
        imagejpeg($img, $path, 90); // Set new quality to 90

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

        // Insert photo in 'activity_photos' table
        $insert_photos = $db->prepare('INSERT INTO activity_photos(activity_id, img_blob, img_size, img_name, img_type, datetime, featured) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $insert_photos -> execute(array($activity_id, $img_blob, $img_size, $img_name, $img_type, $datetime->format('Y-m-d H:i:s'), $featured));
    }

    echo json_encode(true);

}