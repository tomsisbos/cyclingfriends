<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/settings.css" />

<body>
    
<?php

use Abraham\TwitterOAuth\TwitterOAuth;

var_dump($oauth_token);
var_dump($params);
var_dump($_GET);

// If connection has been authorized
if (isset($_GET['oauth_token'])) {
    $oauth_token = $_SESSION['twitter']['request_token']['oauth_token'];
    $oauth_token_secret = $_SESSION['twitter']['request_token']['oauth_token_secret'];
    if ($_GET['oauth_token'] != $oauth_token) throw new Exception('Oauth token mismatch');
    else {
        $oauth = new TwitterOAuth(TWITTER_API_CONSUMER_KEY, TWITTER_API_CONSUMER_SECRET, $oauth_token, $oauth_token_secret);
        $response = $oauth->oauth('oauth/verifier', ['oauth_verifier' => $_GET['oauth_verifier']]);
        var_dump($response);
    }
} ?>

</body>