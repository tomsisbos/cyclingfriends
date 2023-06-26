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
    if (isset($data['bike'])) $bike_id = $data['bike'];
    else $bike_id = null;
    $privacy     = $data['privacy'];

    // Update data in 'activities' table
    $update_activity = $db->prepare('UPDATE activities SET title = ?, bike_id = ?, privacy = ? WHERE id = ?');
    $update_activity->execute(array($title, $bike_id, $privacy, $activity_id));

    // Delete existing checkpoints data
    $delete_checkpoints = $db->prepare('DELETE FROM activity_checkpoints WHERE activity_id = ?');
    $delete_checkpoints->execute(array($activity_id));

    // Build checkpoints data
    foreach ($data['checkpoints'] as $checkpoint) {
        $checkpoint_data['activity_id'] = $activity_id;
        $checkpoint_data['number'] = $checkpoint['number'];
        $checkpoint_data['name'] = $checkpoint['name'];
        $checkpoint_data['type'] = $checkpoint['type'];
        $checkpoint_data['story'] = $checkpoint['story'];
        $checkpoint_data['datetime'] = new DateTime();
        $checkpoint_data['datetime']->setTimestamp($checkpoint['datetime']);
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

    // Get an array containing filenames of photos already uploaded
    $already_uploaded_filenames = [];

    // For each photo
    foreach ($data['photos'] as $photo) {

        // If first photo upload (as photo is posted with a base 64 string), treat/compress data
        if (!empty($photo['blob']) && substr($photo['blob'], 0, 4) == 'data') {
            
            // Get blob ready to upload
            $temp_image = new TempImage($photo['name']);
            $activity_photo_data['blob'] = $temp_image->treatBase64($photo['blob']);

            // Set variables ready for upload
            $activity_photo_data['user_id'] = getConnectedUser()->id;
            $activity_photo_data['activity_id'] = $activity_id;
            $activity_photo_data['size'] = $photo['size'];
            $activity_photo_data['name'] = $photo['name'];
            $activity_photo_data['type'] = $photo['type'];
            $activity_photo_data['lng'] = $photo['lng'];
            $activity_photo_data['lat'] = $photo['lat'];
            $activity_photo_data['datetime'] = new DateTime();
            $activity_photo_data['datetime']->setTimestamp($photo['datetime']);
            $activity_photo_data['datetime']->setTimeZone(new DateTimeZone('Asia/Tokyo'));
            if ($photo['featured'] == true) $activity_photo_data['featured'] = 1;
            else $activity_photo_data['featured'] = 0;
            $activity_photo_data['privacy'] = $photo['privacy'];

            $activity_photo = new ActivityPhoto();
            $filename = $activity_photo->create($activity_photo_data);
            array_push($already_uploaded_filenames, "'" .$filename. "'");
        
        // If photo have already been uploaded formerly
        } else {
        
            // Add file name to filenames array
            if ($photo['filename']) array_push($already_uploaded_filenames, "'" .$photo['filename']. "'");

            // Update featured and privacy entry if necessary
            if ($photo['featured'] == true) $featured = 1;
            else $featured = 0;
            $privacy = $photo['privacy'];
            $updateFeatured = $db->prepare('UPDATE activity_photos SET featured = ?, privacy = ? WHERE filename = ?');
            $updateFeatured->execute(array($featured, $privacy, $photo['filename']));
        }

    }

    // Only delete activity photos that have not been uploaded this time
    $filenames_string = implode(',', $already_uploaded_filenames);
    $delete_photos = $db->prepare("DELETE FROM activity_photos WHERE activity_id = ? AND filename NOT IN ({$filenames_string})");
    $delete_photos->execute(array($activity_id));


    // Create new sceneries if necessary
    if (isset($data['sceneriesToCreate'])) {

        forEach($data['sceneriesToCreate'] as $entry) {
            
            // Get photo blobs and thumbnail ready
            $thumbnail_set = false;
            $thumbnail = null;
            $scenery_data['photos'] = [];
            foreach ($entry['photos'] as $entry_photo) {

                // If filename index exists, get blob from blob storage using filename
                if (isset($entry_photo['filename'])) {
                    $scenery_photo = $entry_photo;
                    $metadata = $blobClient->getBlob('activity-photos', $scenery_photo['filename'])->getMetadata();
                    $scenery_photo['size'] = $metadata['file_size'];
                    $scenery_photo['type'] = $metadata['file_type'];
                    $scenery_photo['name'] = $metadata['file_name'];
                    $base64 = base64_encode(file_get_contents($blobClient->getBlobUrl('activity-photos', $scenery_photo['filename'])));

                // Else, search for corresponding blob using original file name
                } else {
                    foreach ($data['photos'] as $activity_photo) {
                        if (isset($activity_photo['name']) && $activity_photo['name'] == $entry_photo['name']) {
                            $scenery_photo = $entry_photo;
                            $scenery_photo['filename'] = setFilename('img');
                            $base64 = $activity_photo['blob'];
                        }
                    }
                }

                // Get blob ready to upload
                $temp_image = new TempImage($scenery_photo['filename']);
                $scenery_photo['blob'] = $temp_image->treatBase64($base64);

                // Add photo data to scenery photos
                array_push($scenery_data['photos'], $scenery_photo);
                
                // Build and append scenery thumbnail (from first photo blob)
                if (!$thumbnail_set) {
                    $thumbnail = $temp_image->getThumbnail();
                    $thumbnail_set = true;
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
    
    echo json_encode(true);

}