<?php

header('Content-Type: application/json, charset=UTF-8');

// Return false in any case if session does not exist
if (!isset($_SESSION['auth'])) echo json_encode(false); die();

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    // Return info about connected user rights
    if (isset($_GET['has-rights'])) {
        switch ($_GET['has-rights']) {
            case 'premium': $result = $connected_user->isPremium(); break;
            case 'editor': $result = $connected_user->hasEditorRights(); break;
            case 'moderator': $result = $connected_user->hasModeratorRights(); break;
            case 'administrator': $result = $connected_user->hasAdministratorRights(); break;
            default: $result = false;
        }
        echo json_encode($result);
    }

    // Return a particular session value from the key
    if (isset($_GET['get'])) {
        $key = $_GET['get'];
        if (isset($_SESSION[$key])) echo json_encode($_SESSION[$key]);
        else echo json_encode(false);
    }

    // Return profile picture src of connected user
    if (isset($_GET['get-propic'])) echo json_encode($connected_user->getPropicUrl());

    // Return all session information
    if (isset($_GET['get-session'])) {
        if (isset($_SESSION['auth'])) echo json_encode($_SESSION);
        else echo json_encode(false);
    }
}