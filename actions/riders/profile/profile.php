<?php
 
require '../actions/database.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {
		
		// Prepare request of users data of specific id
		$getUser = $db->prepare('SELECT * FROM users WHERE id = ?');
		$getUser->execute(array($slug));
		
		// Check if id exists
		if ($getUser->rowcount() > 0) {		
		
			// If exists, fetch data into $user_data and display the user infos
			$user = new User($slug);
		
		} else {
			
			// If id doesn't exist, redirect to dashboard.php
			header('location: /');
		
		}
	
	} else {
		
		// If id is not set, redirect to dashboard.php
		header('location: /');
		
	} ?>