<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $scenery = new Scenery($data->scenery_id);
    $photo   = $data->photo;

    $photo = [
        'user_id' => $user->id,
        'filename' => $photo->filename,
        'date' => (new DateTime())->setTimestamp(intval($photo->datetime)),
        'blob' => (new TempImage($photo->filename))->treatBase64($photo->base64),
        'name' => $photo->filename,
        'type' => 'image/jpeg',
        'size' => ''
    ];

    $id = $scenery->insertPhoto($photo);
    
    echo json_encode($id);


} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    $scenery_photo = new SceneryImage($data->id);
    $scenery_photo->delete();
    
    echo json_encode(true);

}