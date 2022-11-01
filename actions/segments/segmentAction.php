<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	if (isset($_GET['id'])) {
		
		$segment = new Segment($_GET['id']);
		
		if ($segment->exists()) {
			
			///

        } else {
			
            // If id doesn't exist, redirect to dashboard.php
            header('location: ../dashboard.php');
		
		}
	
	} else {
		
		// If id is not set, redirect to dashboard.php
		header('location: ../dashboard.php');
		
	}
?>