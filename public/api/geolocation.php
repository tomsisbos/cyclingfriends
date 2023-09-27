<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// In case an Ajax request have been detected
if (isset($_GET['lng']) && isset($_GET['lat'])) {

    $coordinates = new LngLat($_GET['lng'], $_GET['lat']);
    $geolocation = $coordinates->queryGeolocation();

    echo json_encode($geolocation);

}