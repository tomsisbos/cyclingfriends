<?php
 
	require '../actions/databaseAction.php';
	
	// Get data from database
	$getRiders = $db->prepare('SELECT id FROM users WHERE city IS NOT NULL AND prefecture IS NOT NULL AND id NOT IN (SELECT user_id FROM settings WHERE hide_on_neighbours = true) AND NOT id = ? ORDER BY id ASC');
	$getRiders->execute(array($connected_user->id));
	$rider_ids = $getRiders->fetchAll(PDO::FETCH_ASSOC);
	$riders = [];
	// Get an array of user sorted by distance from connected user
	foreach ($rider_ids as $rider) {
		$rider = new User($rider['id']);
		$rider->distance = $rider->getDistance($connected_user);
		array_push($riders, $rider);
	}
	usort($riders, function ($a, $b) {
		return $a->distance > $b->distance;
	} );
	 
?>