<?php

require '../actions/database.php';

$rides = [];
$getOfficialRides = $db->prepare("SELECT id FROM rides WHERE privacy = 'public' AND author_id IN (SELECT id FROM users WHERE rights = 'administrator') ORDER BY date > NOW() DESC, date ASC");
$getOfficialRides->execute();

while ($id = $getOfficialRides->fetch(PDO::FETCH_COLUMN)) array_push($rides, new Ride($id));