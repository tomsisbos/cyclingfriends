<?php

// Define query limit
$limit = 50;

$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/databaseAction.php';
	
// Get data from database
$getRiders = $db->prepare("SELECT id FROM users WHERE city IS NOT NULL AND prefecture IS NOT NULL AND id NOT IN (SELECT id FROM settings WHERE hide_on_neighbours = true) AND NOT id = ? ORDER BY id ASC");
$getRiders->execute(array($connected_user->id));
$rider_ids = $getRiders->fetchAll(PDO::FETCH_ASSOC);
$riders = [];

// Get an array of user sorted by distance from connected user
for ($i = 0; $i < $limit && $i < $getRiders->rowCount(); $i++) {
	$rider = new User($rider_ids[$i]['id']);
	$rider->distance = $rider->getDistance($connected_user);
	array_push($riders, $rider);
}
usort($riders, function ($a, $b) {
	return $a->distance <=> $b->distance;
} );
	 
?>