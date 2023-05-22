<?php

$twitter = new Twitter();
$redirect_url = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . '/api/twitter/connection.php';
$twitter_auth_url = $twitter->getAuthenticateUrl($redirect_url);