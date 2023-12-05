<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $variable_name = 'photo';
    $photos = [];

    // Extract variables from form data
    foreach ($_POST as $keystring => $value) {
        preg_match('!\d+!', $keystring, $match);
        $index = intval($match[0]);
        $key = substr($keystring, strlen($variable_name) + strlen($match[0]) + 1);
        // Extract blob
        if ($key == 'blob') $photos[$index]['blob'] = $value;
        // Extract other json_encoded data
        else if ($key == 'data') {
            $data = json_decode($value);
            foreach ($data as $data_key => $data_value) $photos[$index][$data_key] = $data_value;
        }
    }

    try {

        $author_id = $photos[0]['author_id'];
        $activity_id = $photos[0]['activity_id'];

        // Delete previous photos
        $deletePreviousPhotos = $db->prepare("DELETE FROM activity_photos WHERE activity_id = ?");
        $deletePreviousPhotos->execute(array($activity_id));

        foreach ($photos as $photo) {
            
            include $base_directory . '/actions/activities/photoTreatment.php';

        }

        ///echo json_encode(['success' => count($photos). '枚の写真が無事にアップロードされました。']);

    } catch (Exception $e) {

        echo json_encode(['error' => $e->getMessage()]);

    }

} else echo 'Invalid request method.'; ?>
