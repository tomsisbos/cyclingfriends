<?php
header('Content-Type: application/json, charset=UTF-8');
session_start();
require $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php';
Autoloader::register();
include $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
include $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['add'])) {
        $user = new User($_GET['add']);
        $response = $connected_user->sendFriendRequest($user);
        echo json_encode($response);
    }

    if (isset($_GET['accept'])) {
        $user = new User($_GET['accept']);
        $response = $connected_user->acceptFriendRequest($user);
        echo json_encode($response);
    }

    if (isset($_GET['dismiss'])) {
        $user = new User($_GET['dismiss']);
        $result = $connected_user->removeFriend($user);
        if ($result[0]) {
            $response = array(true, 'You have declined ' .$user->login .'\'s invitation.');
        } else {
            $response = array(false, $user->login .' didn\'t send you any friend request.');
        }
        echo json_encode($response);
    }

    if (isset($_GET['remove'])) {
        $user = new User($_GET['remove']);
        $response = $connected_user->removeFriend($user);
        echo json_encode($response);
    }

}