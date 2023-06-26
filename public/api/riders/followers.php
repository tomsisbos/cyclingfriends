<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['follow'])) {
        $user = new User($_GET['follow']);
        $response = getConnectedUser()->follow($user);
        echo json_encode($response);
    }

    if (isset($_GET['unfollow'])) {
        $user = new User($_GET['unfollow']);
        $response = getConnectedUser()->unfollow($user);
        echo json_encode($response);
    }

}