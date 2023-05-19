<?php

use Abraham\TwitterOAuth\TwitterOAuth;

$oauth = new TwitterOAuth(TWITTER_API_CONSUMER_KEY, TWITTER_API_CONSUMER_SECRET);
$request_token = $oauth->oauth('oauth/request_token', ['oauth_callback' => 'https://cyclingfriends-preprod.azurewebsites.net/twitter/connection']);
$twitter_auth_url = $oauth->url('oauth/authenticate', ['oauth_token' => $request_token['oauth_token']]);
$_SESSION['twitter']['request_token'] = $request_token;
include '../includes/twitter/connection-button.php';