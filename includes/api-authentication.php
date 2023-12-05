<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register();
require_once $base_directory . '/includes/functions.php';
require_once $base_directory . '/includes/session-handlers.php';
require_once $base_directory . '/actions/users/initPublicSession.php';
require_once $base_directory . '/actions/database.php';


// Check if request is coming from $allowed_hosts, which doesn't require a JWT token
$is_allowed = false;
$allowed_hosts = [
    'cyclingfriends.co',
    'www.cyclingfriends.co',
    'cyclingfriends.azurewebsites.net',
    'cyclingfriends-preprod.azurewebsites.net'
];

if (isset($_SERVER['HTTP_REFERER'])) {
    $host = parse_url($_SERVER['HTTP_REFERER'])['host'];
    if (in_array($host, $allowed_hosts)) {
        $is_allowed = true;
        $user = getConnectedUser();
    }
}

$token = $_SERVER['HTTP_AUTHORIZATION'];

// If it isn't, verify the JWT token first
if (!$is_allowed) require_once $base_directory . '/actions/users/tokenCheck.php';