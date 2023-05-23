<?php

require '../../../includes/api-head.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    $twitter = $connected_user->getTwitter();
    if ($twitter->isUserConnected()) {
        $result = $twitter->post($data['text']);
        echo json_encode($result);
    } else throw new Exception('接続が切れました。投稿できませんでした。');

}