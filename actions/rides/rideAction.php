<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);

	if ($slug === 'join' || $slug === 'quit') {

		$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
		$slug = array_slice($url_fragments, -2)[0];

	}
	
	// Check if ride exists
	if (is_numeric($slug)) {
		
        $checkIfExists = $db->prepare('SELECT id FROM rides WHERE id = ?');
        $checkIfExists->execute([$slug]);

		// If exists, get ride data
        if ($checkIfExists->rowCount() > 0) {

			include '../actions/rides/edit/adminPanelAction.php';

			$ride = new Ride($slug);
			
			// If ride admin have submitted data, then replace existing data by submitted one
			if (isset($_POST['save'])) {
				$ride->privacy     = $_POST['privacy'];
				$ride->entry_start = $_POST['entry_start'];
				$ride->entry_end   = $_POST['entry_end'];
			}
		
		} else {
			
		// If id doesn't exist, redirect to my rides page
		header('location: ' .$router->generate('ride-organizations'));
		
		}
	
	} else {
		
		// If id is not set, redirect to my rides page
		header('location: ' .$router->generate('ride-organizations'));
		
	}
?>