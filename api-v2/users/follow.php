<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $to_follow = new User($data->user_id);

    if ($user->follows($to_follow)) $response = $user->unfollow($to_follow);
    else $response = $user->follow($to_follow);

    echo json_encode($response);

}