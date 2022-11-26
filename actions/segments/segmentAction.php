<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

        $getSeasons = $db->prepare('SELECT id FROM segments WHERE id = ?');
        $getSeasons->execute(array($slug));
		
		if ($getSeasons->rowCount() > 0) {
			
			$segment = new Segment($slug);

        } else {
			
            // If id doesn't exist, redirect to dashboard.php
            header('location: /');
		
		}
	
	} else {
		
		// If id is not set, redirect to dashboard.php
		header('location: /');
		
	}
?>