<?php
 
	require 'actions/databaseAction.php';
					
	// If min date filter is empty, set it as current date
	if (empty($_POST['filter_date_min'])) {
		$_POST['filter_date_min'] = date('Y-m-d');
	}
		
	// If max date filter is empty, set it as 2099-12-31
	if (empty($_POST['filter_date_max'])) {
		$_POST['filter_date_max'] = date('2099-12-31');
	}
	
	if (empty($_POST['filter_level'])) {
		$_POST['filter_level'] = 'No filter';
	}
	
	if (empty($_POST['filter_friends_only'])) {
		$_POST['filter_friends_only'] = 'No filter';
	}
	
	if (empty($_POST['filter_status'])) {
		$_POST['filter_status'] = 'No filter';
	}
	
	if (empty($_POST['filter_name'])) {
		$_POST['filter_name'] = '%';
	}
		
	// Get data from database.
		// Use "status = status" to display all results in case of no filter
		// Puts Friends only rides at the top thanks to the order by case query
	$getRides = $db->prepare("SELECT * FROM rides WHERE 
		privacy != 'private'
			AND 
		date BETWEEN :datemin AND :datemax 
			AND 
		name LIKE :name 
			AND	
			(CASE 
				WHEN :level = 'Beginner' THEN level_beginner 
				WHEN :level = 'Intermediate' THEN level_intermediate
				WHEN :level = 'Athlete' THEN level_athlete 
				ELSE true 
			END) = TRUE 
			AND
			(CASE 
				WHEN :status = 'Open' THEN status LIKE 'Open%' 
				WHEN :status = 'No filter' THEN status = status 
				ELSE status = :status 
			END)
			AND
			(CASE
				WHEN :friends = 'No filter' THEN true
				ELSE author_id IN ('".implode("','",$connected_user->getFriends())."')
			END)
		ORDER BY 
			(CASE 
				WHEN privacy = 'Friends only' THEN 0
				ELSE 1
			END),
			date, meeting_time ASC");
		
	$getRides->execute(array(":name" => '%' .$_POST['filter_name']. '%', ":datemin" => $_POST['filter_date_min'], ":datemax" =>  $_POST['filter_date_max'], ":level" => $_POST['filter_level'], ":status" => $_POST['filter_status'], ":friends" => $_POST['filter_friends_only']));
	 
?>