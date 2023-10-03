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

			// If access is not allowed, redirect to activities.php
			if (!$activity->hasAccess(getConnectedUser())) header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/activities');       

		// If id doesn't exist, redirect to myactivities.php
		} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/activities');
	
	// If id is not set, redirect to myactivities.php
	} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/activities');
	
?>