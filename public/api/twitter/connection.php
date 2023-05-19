<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register();
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/users/initSessionAction.php';
require $base_directory . '/actions/databaseAction.php';

use Abraham\TwitterOAuth\TwitterOAuth;

// If connection has been authorized
if (isset($_GET['oauth_token'])) {
    $twitter = new Twitter(getenv('TWITTER_API_CONSUMER_KEY'), getenv('TWITTER_API_CONSUMER_SECRET'));
    $tokens = $twitter->getAccessToken($_GET['oauth_token'], $_GET['oauth_verifier']);
    $twitter->saveAccessToken($connected_user->id, $tokens['oauth_token'], $tokens['oauth_token_secret']);
    if ($twitter->verifyCredentials($tokens['oauth_token'], $tokens['oauth_token_secret']));
} ?>