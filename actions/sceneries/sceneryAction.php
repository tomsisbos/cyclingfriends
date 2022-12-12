<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

        $getMkpoint = $db->prepare('SELECT id FROM map_mkpoint WHERE id = ?');
        $getMkpoint->execute(array($slug));
		
		if ($getMkpoint->rowCount() > 0) {
			
			$mkpoint = new Mkpoint($slug);

        } else {
			
            // If id doesn't exist, redirect to dashboard.php
            header('location: /');
		
		}
	
	} else {
		
		// If id is not set, redirect to dashboard.php
		header('location: /');
		
	}
?>