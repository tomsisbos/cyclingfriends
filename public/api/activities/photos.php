<?php

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $photos = $_FILES;

    echo json_encode(['$_POST' => $_POST, '$_FILES' => $_FILES]);

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
