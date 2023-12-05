<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $activity = new Activity($data->id);
    $activity->toggleLike($user->id);

    echo json_encode($activity->getLikes());

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $activity_id = $_GET['id'];

    $activity = new Activity($activity_id);

    echo json_encode($activity->getLikes());

}