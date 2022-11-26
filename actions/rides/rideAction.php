<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	$slug = basename($_SERVER['REQUEST_URI']);

	if ($slug === 'join' || $slug === 'quit') {

		$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
		$slug = array_slice($url_fragments, -2)[0];

	}
	
	// Instantiate Ride class
	if (is_numeric($slug)) {
		
		$ride = new Ride($slug);
		
		if ($ride->exists()) {
		
			// If exists, fetch data into $ride_data and display the ride infos
			// $ride = $getRide->fetch(); 
			
			// If ride admin have submitted data, then replace existing data by submitted one
			if (isset($_POST['save'])) {
				$ride->privacy     = $_POST['privacy'];
				$ride->entry_start = $_POST['entry_start'];
				$ride->entry_end   = $_POST['entry_end'];
			}
		
		} else {
			
		// If id doesn't exist, redirect to my rides page
		header('location: /' . $connected_user->login . '/rides');
		
		}
	
	} else {
		
	// If id is not set, redirect to my rides page
		header('location: /' . $connected_user->login . '/rides');
		
	}
?>