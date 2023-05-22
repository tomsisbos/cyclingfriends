<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register();
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/users/initSessionAction.php';
require $base_directory . '/actions/databaseAction.php';

use Abraham\TwitterOAuth\TwitterOAuth;

// 
if (!isset($_SESSION['auth'])) {
    header('location: /signin');
}

// If connection has been authorized
if (isset($_GET['oauth_token'])) {
    $twitter = new Twitter();
    $user_data = $twitter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);
    $twitter->saveUserData($connected_user->id, $user_data);
    $twitter_user_info = $twitter->verifyCredentials($user_data['oauth_token'], $user_data['oauth_token_secret']);
    if ($twitter_user_info) {
        $_SESSION['successmessage'] = '<a href="https://twitter.com/' .$twitter_user_info->screen_name. '" target="_blank">@' .$twitter_user_info->screen_name. '</a>と接続できました！';
        if (isset($_SESSION['redirect_uri'])) {
            $uri = $_SESSION['redirect_uri'];
            unset($_SESSION['redirect_uri']);
            header('location: ' .$uri);
        } else header('location: /profile/edit');
    }
    else throw new Error('Oauth token mismatch');
} ?>