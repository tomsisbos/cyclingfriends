<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $base64 = $data->base64;
    $bike_id = $data->id;

    $filename = setFilename('img');
    $temp_image = new TempImage($filename);
    $blob = $temp_image->treatBase64($base64);
    
    // Connect to blob storage
    $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
    require $folder . '/actions/blobStorage.php';

    // Send file to blob storage
    $containername = 'user-profile-bikes';
    $blobClient->createBlockBlob($containername, $filename, $blob);

    // Set file metadata
    $metadata = [
        'bike_id' => $bike_id,
        'user_id' => $user->id
    ];
    $blobClient->setBlobMetadata($containername, $filename, $metadata);

    $updateImage = $db->prepare('UPDATE bikes SET filename = ? WHERE id = ?');
    $updateImage->execute([$filename, $bike_id]);

    echo json_encode($filename);
}