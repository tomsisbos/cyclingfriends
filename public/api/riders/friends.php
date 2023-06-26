<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['add'])) {
        $user = new User($_GET['add']);
        $response = getConnectedUser()->sendFriendRequest($user);
        echo json_encode($response);
    }

    if (isset($_GET['accept'])) {
        $user = new User($_GET['accept']);
        $response = getConnectedUser()->acceptFriendRequest($user);
        echo json_encode($response);
    }

    if (isset($_GET['dismiss'])) {
        $user = new User($_GET['dismiss']);
        $result = getConnectedUser()->removeFriend($user);
        if ($result[0]) {
            $response = array(true, $user->login .'の友達申請を却下しました。');
        } else {
            $response = array(false, $user->login .'は友達申請を送っていません。');
        }
        echo json_encode($response);
    }

    if (isset($_GET['remove'])) {
        $user = new User($_GET['remove']);
        $response = getConnectedUser()->removeFriend($user);
        echo json_encode($response);
    }

}