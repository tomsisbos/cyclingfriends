<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);
	if (is_numeric($slug)) {

        $getScenery = $db->prepare('SELECT id FROM sceneries WHERE id = ?');
        $getScenery->execute(array($slug));
		
		if ($getScenery->rowCount() > 0) {
			
			$scenery = new Scenery($slug);

        } else {
			
            // If id doesn't exist, redirect to dashboard.php
            header('location: /');
		
		}
	
	} else {
		
		// If id is not set, redirect to dashboard.php
		header('location: /');
		
	}
?>