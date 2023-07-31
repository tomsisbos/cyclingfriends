<?php

require '../actions/database.php';

$rides = [];
$getOfficialRides = $db->prepare("SELECT id FROM rides WHERE author_id IN (SELECT id FROM users WHERE rights = 'administrator') ORDER BY date ASC");
$getOfficialRides->execute();


while ($id = $getOfficialRides->fetch(PDO::FETCH_COLUMN)) array_push($rides, new Ride($id));