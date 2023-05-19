<?php

$twitter = new Twitter(getenv('TWITTER_API_CONSUMER_KEY'), getenv('TWITTER_API_CONSUMER_SECRET'));
$twitter_auth_url = $twitter->getAuthenticateUrl('https://cyclingfriends-preprod.azurewebsites.net/api/twitter/connection.php');

include '../includes/twitter/connection-button.php';