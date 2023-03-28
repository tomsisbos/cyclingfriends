<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/Autoloader.php'; 
Autoloader::register();
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/users/initPublicSessionAction.php';
require $base_directory . '/actions/databaseAction.php';


// Only allow request coming from $allowed_hosts
$is_allowed = false;
$allowed_hosts = [
    'cyclingfriends.co',
    'www.cyclingfriends.co',
    'cyclingfriends.azurewebsites.net'
];

if (isset($_SERVER['HTTP_REFERER'])) {
    $host = parse_url($_SERVER['HTTP_REFERER'])['host'];
    if (in_array($host, $allowed_hosts)) $is_allowed = true;
}

if (!$is_allowed) {
    echo "You are not allowed to access this resource.";
    die();
}