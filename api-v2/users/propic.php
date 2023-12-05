<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $filename = setFilename('img');
    $temp_image = new TempImage($filename);
    $blob = $temp_image->treatBase64($data->base64);
    
    // Connect to blob storage
    $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
    require $folder . '/actions/blobStorage.php';

    // Send file to blob storage
    $containername = 'user-profile-pictures';
    $blobClient->createBlockBlob($containername, $filename, $blob);

    // Set file metadata
    $metadata = [
        'user_id' => $user->id,
        'datetime' => (new DateTime('now', new DateTimezone('Asia/Tokyo')))->format('Y-m-d H:i:s')
    ];
    $blobClient->setBlobMetadata($containername, $filename, $metadata);

    // Check if connected user has already uploaded a picture
    $checkUserId = $db->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
    $checkUserId->execute(array($user->id));

    // If he does, update data in the database
    if ($checkUserId->rowCount() > 0) {
        $updateImage = $db->prepare('UPDATE profile_pictures SET filename = ? WHERE user_id = ?');
        $updateImage->execute(array($filename, $user->id));

    // If he doesn't, insert a new line into the database
    } else {
        $insertImage = $db->prepare('INSERT INTO profile_pictures (user_id, filename) VALUES (?, ?)');
        $insertImage->execute(array($user->id, $filename));
    }

}