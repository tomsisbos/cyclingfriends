<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register();
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/users/initSessionAction.php';
require $base_directory . '/actions/databaseAction.php';

use Stoufa\GarminApi\GarminApi;

// 
if (!isSessionActive()) {
    header('location: /signin');
}

// If connection has been authorized
if (isset($_GET['oauth_token'])) {
    
    try {

        $garmin = new Garmin();
        $temporary_credentials = $_SESSION['garmin_temporary_credentials'];
        unset($_SESSION['garmin_temporary_credentials']);

        // Get user token credentials and id
        $user_data = $garmin->getAccessToken($temporary_credentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
        $garmin->saveUserData(getConnectedUser()->id, $user_data);
        
        $_SESSION['successmessage'] = 'Garmin Connectと接続できました！';
        if (isset($_SESSION['redirect_uri'])) {
            $uri = $_SESSION['redirect_uri'];
            unset($_SESSION['redirect_uri']);
            header('location: ' .$uri);
        } else header('location: /settings');
        
    } catch (\Throwable $th) {
        echo 'ERROR2';
        var_dump($th);
        // catch your exception here
    }
} ?>