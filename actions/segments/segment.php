<?php
 
	require '../actions/database.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

        $getSegment = $db->prepare('SELECT id FROM segments WHERE id = ?');
        $getSegment->execute(array($slug));
		
		if ($getSegment->rowCount() > 0) {
			
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