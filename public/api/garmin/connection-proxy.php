<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

$token = $_GET['token'];

require_once $base_directory . '/actions/users/tokenCheck.php';

$garmin = new Garmin();
$_SESSION['redirect_uri'] = 'https://www.cyclingfriends.co/garmin/success';
$auth_url = $garmin->getAuthenticateUrl($user->id);
header('location: ' .$auth_url);