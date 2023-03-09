<?php

require '../../../includes/api-head.php';

// Connect to blob storage
$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/blobStorageAction.php';

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

    // Get an array containing filenames of photos already uploaded
    $filenames = [];

    // For each photo
    foreach ($data['photos'] as $photo) {

        // If first photo upload (as photo is posted with a base 64 string), treat/compress data
        if (!empty($photo['blob']) && substr($photo['blob'], 0, 4) == 'data') {
            
            // Get blob ready to upload
            $temp_image = new TempImage($photo['name']);
            $img_blob = $temp_image->treatBase64($photo['blob']);

            // Set variables ready for upload
            $filename = setFilename('img');
            $img_size = $photo['size'];
            $img_name = $photo['name'];
            $img_type = $photo['type'];
            $lng = $photo['lng'];
            $lat = $photo['lat'];
            $datetime = new DateTime();
            $datetime->setTimestamp($photo['datetime'] / 1000);
            $datetime->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            if ($photo['featured'] == true) $featured = 1;
            else $featured = 0;
            
            // Insert photo in 'activity_photos' table
            $insert_photos = $db->prepare('INSERT INTO activity_photos(activity_id, user_id, datetime, featured, lng, lat, filename) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $insert_photos -> execute(array($activity_id, $connected_user->id, $datetime->format('Y-m-d H:i:s'), $featured, $lng, $lat, $filename));
            
            // Send file to blob storage
            $containername = 'activity-photos';
            $blobClient->createBlockBlob($containername, $filename, $img_blob);
            // Set file metadata
            $metadata = [
                'file_name' => $img_name,
                'file_type' => $img_type,
                'file_size' => $img_size,
                'activity_id' => $activity_id,
                'author_id' => $connected_user->id,
                'date' => $datetime->format('Y-m-d H:i:s'),
                'lng' => $lng,
                'lat' => $lat
            ];
            $blobClient->setBlobMetadata($containername, $filename, $metadata);
        
        // If photo have already been uploaded formerly
        } else {
        
            // Add file name to filenames array
            if ($photo['filename']) array_push($filenames, "'" .$photo['filename']. "'");

            // Update featured entry if necessary
            if ($photo['featured'] == true) $featured = 1;
            else $featured = 0;
            $updateFeatured = $db->prepare('UPDATE activity_photos SET featured = ? WHERE filename = ?');
            $updateFeatured -> execute(array($featured, $photo['filename']));
        
        }

    }

    // Only delete activity photos that have not been uploaded this time
    $filenames_string = implode(',', $filenames);
    $delete_photos = $db->prepare("DELETE FROM activity_photos WHERE activity_id = ? AND filename NOT IN ({$filenames_string})");
    $delete_photos->execute(array($activity_id));


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
            foreach ($entry['photos'] as $mkpoint_photo) {

                // If filename index exists, get blob from blob storage using filename
                if (isset($mkpoint_photo['filename'])) {
                    $metadata = $blobClient->getBlob('activity-photos', $mkpoint_photo['filename'])->getMetadata();
                    $mkpoint_photo['size'] = $metadata['file_size'];
                    $mkpoint_photo['type'] = $metadata['file_type'];
                    $mkpoint_photo['name'] = $metadata['file_name'];
                    $base64 = 'data:' .$mkpoint_photo['type']. ';base64,' .base64_encode(file_get_contents($blobClient->getBlobUrl('activity-photos', $mkpoint_photo['filename'])));

                // Else, search for corresponding blob using original file name
                } else {
                    foreach ($data['photos'] as $activity_photo) {
                        if (isset($activity_photo['name']) && $activity_photo['name'] == $mkpoint_photo['name']) $base64 = $activity_photo['blob'];
                    }
                }

                // Get blob ready to upload
                $temp_image = new TempImage($photo['name']);
                $mkpoint_photo['filename'] = setFilename('img');
                $mkpoint_photo['blob'] = $temp_image->treatBase64($photo['blob']);

                // Build and append mkpoint thumbnail
                if (!$thumbnail_set) {

                    // Get thumbnail
                    $mkpoint['thumbnail'] = $temp_image->getThumbnail();

                    // Insert mkpoint data
                    $insertMkpointThumbnail = $db->prepare('UPDATE map_mkpoint SET thumbnail = ? WHERE id = ?');
                    $insertMkpointThumbnail->execute(array($mkpoint['thumbnail'], $mkpoint['id']));
                    $thumbnail_set = true;
                }

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

            // Insert tags data
            if (!empty($entry['tags'][0])) {
                foreach ($entry['tags'] as $tag) {
                    $insertTag = $db->prepare('INSERT INTO tags (object_type, object_id, tag) VALUES (?, ?, ?)');
                    $insertTag->execute(array('scenery', $mkpoint['id'], $tag));
                }
            }
        }
    }
    
    echo json_encode(true);

}