<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// In case an Ajax request have been detected
if (isset($_GET['rental_bike_id'])) {

    if (is_numeric($_GET['rental_bike_id'])) {
        $rental_bike = new RentalBike($_GET['rental_bike_id']);
        echo json_encode($rental_bike);
    } else echo json_encode(false);

}