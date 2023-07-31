<?php
 
	require '../actions/database.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

		// Check if activity exists
		$checkIfActivityExists = $db->prepare('SELECT id FROM activities WHERE id = ?');
		$checkIfActivityExists->execute([$slug]);
		if ($checkIfActivityExists->rowCount() > 0) {
			
			$activity = new Activity($slug);

			// If id doesn't exist, redirect to myactivities.php
			if (!$activity->hasAccess(getConnectedUser())) header('location: /' . getConnectedUser()->login . '/activities');       

		// If id doesn't exist, redirect to myactivities.php
		} else header('location: /activities');
	
	// If id is not set, redirect to myactivities.php
	} else header('location: /activities');
	
?>