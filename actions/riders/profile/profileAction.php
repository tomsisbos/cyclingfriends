<?php
 
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
	
	// Get id from URL
	if (isset($_GET['id'])) {
		
		// Prepare request of users data of specific id
		$getUser = $db->prepare('SELECT * FROM users WHERE id = ?');
		$getUser->execute(array($_GET['id']));
		
		// Check if id exists
		if ($getUser->rowcount() > 0) {		
		
			// If exists, fetch data into $user_data and display the user infos
			$user = new User ($_GET['id']);
		
		} else {
			
		// If id doesn't exist, redirect to dashboard.php
		header('location: ../dashboard.php');
		
		}
	
	} else {
		
	// If id is not set, redirect to dashboard.php
	header('location: ../dashboard.php');
		
	}
?>