<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

		// Check if activity exists
		$checkIfActivityExists = $db->prepare('SELECT id FROM activities WHERE id = ?');
		$checkIfActivityExists->execute([$slug]);
		if ($checkIfActivityExists->rowCount() > 0) {
			
			$activity = new Activity($slug);

			// If id doesn't exist, redirect to myactivities.php
			if (!$activity->hasAccess($connected_user)) header('location: /' . $connected_user->login . '/activities');       

		// If id doesn't exist, redirect to myactivities.php
		} else header('location: /' . $connected_user->login . '/activities');
	
	// If id is not set, redirect to myactivities.php
	} else header('location: /' . $connected_user->login . '/activities');
	
?>