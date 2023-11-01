<?php

require_once '../actions/database.php';

$getRentalBikes = $db->prepare("SELECT id FROM rental_bikes WHERE available = true ORDER BY price_ride DESC");
$getRentalBikes->execute();
$rental_bikes = array_map(function ($rental_bike_id) {
    return new RentalBike($rental_bike_id);
}, $getRentalBikes->fetchAll(PDO::FETCH_COLUMN));