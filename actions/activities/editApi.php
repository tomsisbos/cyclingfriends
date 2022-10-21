<?php
session_start();
// Autoload
require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php'; 
Autoloader::register(); 
require $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {

    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    // Build activity data
    $activity_id = $data['id'];
    $title       = $data['title'];
    $bike_id     = $data['bike'];
    $privacy     = $data['privacy'];

    // Update data in 'activities' table
    $update_activity = $db->prepare('UPDATE activities SET title = ?, bike_id = ?, privacy = ? WHERE id = ?');
    $update_activity->execute(array($title, $bike_id, $privacy, $activity_id));

    // Delete existing checkpoints data
    $delete_checkpoints = $db->prepare('DELETE FROM activity_checkpoints WHERE activity_id = ?');
    $delete_checkpoints->execute(array($activity_id));

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

    // Delete existing photos data
    $delete_photos = $db->prepare('DELETE FROM activity_photos WHERE activity_id = ?');
    $delete_photos->execute(array($activity_id));

    // For each photo
    foreach ($data['photos'] as $photo) {

        // If first upload (as photo is posted with a base 64 string), treat/compress data
        if (substr($photo['blob'], 0, 4) == 'data') {
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
            imagejpeg($img, $path, 75); // Set new quality to 75

            // Build photo data
            $img_blob = base64_encode(file_get_contents($path));

        // If photo has already been treated (if have not been modified), keep it as it is
        } else $img_blob = $photo['blob'];
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