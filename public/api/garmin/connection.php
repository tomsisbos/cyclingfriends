<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

use Stoufa\GarminApi\GarminApi;
use League\OAuth1\Client\Credentials\TemporaryCredentials;

if (isset($_SESSION['garmin_temporary_user_id'])) {
    $user = new User($_SESSION['garmin_temporary_user_id']);
    $temporary_credentials = $_SESSION['garmin_temporary_credentials'];
    unset($_SESSION['garmin_temporary_user_id']);
    unset($_SESSION['garmin_temporary_credentials_identifier']);

} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/signin');


// If connection has been authorized
if (isset($_GET['oauth_token'])) {

    $garmin = new Garmin();

    // Get user token credentials and id
    $user_data = $garmin->getAccessToken($temporary_credentials, $_GET['oauth_token'], $_GET['oauth_verifier']);
    $garmin->saveUserData($user->id, $user_data);
    
    $_SESSION['successmessage'] = 'Garmin Connectと接続できました！';
    if (isset($_SESSION['redirect_uri'])) {
        $uri = $_SESSION['redirect_uri'];
        unset($_SESSION['redirect_uri']);
        header('location: ' .$uri);
    } else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/settings');
        
} ?>