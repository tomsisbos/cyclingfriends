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

    if (isset($_GET['follow'])) {
        $user = new User($_GET['follow']);
        $response = $connected_user->follow($user);
        echo json_encode($response);
    }

    if (isset($_GET['unfollow'])) {
        $user = new User($_GET['unfollow']);
        $response = $connected_user->unfollow($user);
        echo json_encode($response);
    }

}