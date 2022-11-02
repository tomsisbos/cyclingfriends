<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	if (isset($_GET['id'])) {

        $getSeasons = $db->prepare('SELECT id FROM segments WHERE id = ?');
        $getSeasons->execute(array($_GET['id']));
		
		if ($getSeasons->rowCount() > 0) {
			
			$segment = new Segment($_GET['id']);

        } else {
			
            // If id doesn't exist, redirect to dashboard.php
            header('location: ../dashboard.php');
		
		}
	
	} else {
		
		// If id is not set, redirect to dashboard.php
		header('location: ../dashboard.php');
		
	}
?>