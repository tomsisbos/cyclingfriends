<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $updateUserPushToken = $db->prepare('UPDATE users SET push_token = ? WHERE id = ?');
    $updateUserPushToken->execute(array($data->push_token, $data->user_id));

    echo json_encode(true);

}