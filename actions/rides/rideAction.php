<?php
 
	require '../actions/databaseAction.php';
	
	// Get id from URL
	if (isset($_GET['id'])) {
		
		$ride = new Ride ($_GET['id']);
		
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
			
		// If id doesn't exist, redirect to myrides.php
		header('location: ../rides/myrides.php');
		
		}
	
	} else {
		
	// If id is not set, redirect to myrides.php
	header('location: ../rides/myrides.php');
		
	}
?>