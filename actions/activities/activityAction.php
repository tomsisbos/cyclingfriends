<?php
 
	require 'actions/databaseAction.php';
	
	// Get id from URL
	if (isset($_GET['id'])) {
		
		$activity = new Activity($_GET['id']);
		
		if ($activity->exists()) {

			if ($activity->hasAccess($connected_user)) {
				
				/*
				// If ride admin have submitted data, then replace existing data by submitted one
				if (isset($_POST['save'])) {
					$activity->privacy     = $_POST['privacy'];
					$activity->entry_start = $_POST['entry_start'];
					$activity->entry_end   = $_POST['entry_end'];
				}
				*/

			} else {

				// If id doesn't exist, redirect to myactivities.php
				header('location: activities/myactivities.php');

			}            

        } else {
			
            // If id doesn't exist, redirect to myactivities.php
            header('location: activities/myactivities.php');
		
		}
	
	} else {
		
	// If id is not set, redirect to myactivities.php
	header('location: activities/myactivities.php');
		
	}
?>