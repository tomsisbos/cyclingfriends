<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require_once $base_directory . '/includes/session-handlers.php';
require_once $base_directory . '/actions/users/initPublicSession.php';
require_once $base_directory . '/includes/functions.php';
require_once $base_directory . '/actions/database.php';

if (isset($_SERVER['HTTP_AUTHORIZATION'])) {

    $token = $_SERVER['HTTP_AUTHORIZATION'];
    
    if (!empty($token) && $token != 'null') {
        $key = getEnv('JWT_SECRET_KEY');
        $user_data = JWT::decode($token, new Key($key, 'HS256'));
        $user = new User($user_data->id);
    }

}