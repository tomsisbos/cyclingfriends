<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// In case an Ajax request have been detected
if (isset($_GET['user_id'])) {

    $user = new User($_GET['user_id']);
    $bike_ids = $user->getBikes();

    $bikes = array_map(function ($bike_id) {
        return new Bike($bike_id);
    }, $bike_ids);

    echo json_encode($bikes);
}