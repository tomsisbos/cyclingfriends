<?php

$twitter = new Twitter(getenv('TWITTER_API_CONSUMER_KEY'), getenv('TWITTER_API_CONSUMER_SECRET'));
$redirect_url = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . '/api/twitter/connection.php';
$twitter_auth_url = $twitter->getAuthenticateUrl($redirect_url);

include '../includes/twitter/connection-button.php';