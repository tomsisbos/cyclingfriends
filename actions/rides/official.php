<?php


require '../actions/database.php';

$getOfficialRides = $db->prepare("SELECT id FROM rides WHERE privacy = 'public' AND author_id IN (SELECT id FROM users WHERE rights = 'administrator') ORDER BY date > NOW() DESC, date ASC");
$getOfficialRides->execute();

$rides = array_map(function ($id) {
    return new Ride($id);
}, $getOfficialRides->fetchAll(PDO::FETCH_COLUMN));