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

    foreach ($_POST as $key => $value) {
        preg_match('!\d+!', $key, $match);
        $index = intval($match[0]);
        $photos[$index][substr($key, strlen($variable_name) + strlen($match[0]) + 1)] = $value;
    }

    try {

        foreach ($photos as $photo) {
    
            $author_id = $photo['author_id'];
            $activity_id = $photo['activity_id'];
            
            include $base_directory . '/actions/activities/photoTreatment.php';

        }

            echo json_encode(['success' => count($photos). '枚の写真が無事にアップロードされました。']);

    } catch (Exception $e) {

        echo json_encode(['error' => $e->getMessage()]);

    }

} else echo 'Invalid request method.'; ?>
