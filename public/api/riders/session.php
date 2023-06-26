<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// Return false in any case if session does not exist
if (!isset($_SESSION['auth'])) {
    echo json_encode(false);
    die();
}

// In case an Ajax request have been detected
if (isAjax()) {

    // Return info about connected user rights
    if (isset($_GET['has-rights'])) {
        switch ($_GET['has-rights']) {
            case 'premium': $result = getConnectedUser()->isPremium(); break;
            case 'editor': $result = getConnectedUser()->hasEditorRights(); break;
            case 'moderator': $result = getConnectedUser()->hasModeratorRights(); break;
            case 'administrator': $result = getConnectedUser()->hasAdministratorRights(); break;
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
    if (isset($_GET['get-propic'])) echo json_encode(getConnectedUser()->getPropicUrl());

    // Return all session information
    if (isset($_GET['get-session'])) {
        if (isset($_SESSION['auth'])) echo json_encode($_SESSION);
        else echo json_encode(false);
    }
}