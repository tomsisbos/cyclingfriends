<?php
 
	require '../actions/databaseAction.php';
					
	// If min date filter is empty, set it as current date
	if (empty($_POST['filter_date_min'])) $_POST['filter_date_min'] = date('1970-01-01');
		
	// If max date filter is empty, set it as 2099-12-31
	if (empty($_POST['filter_date_max'])) $_POST['filter_date_max'] = date('2099-12-31');
	
	if (empty($_POST['filter_level'])) $_POST['filter_level'] = 'No filter';
	
	if (empty($_POST['filter_friends_only'])) $_POST['filter_friends_only'] = 'No filter';
	
	if (empty($_POST['filter_status'])) $_POST['filter_status'] = 'No filter';
	
	if (empty($_POST['filter_name'])) {
		$_POST['filter_name'] = '%';
	}
		
	// Get data from database.
		// Use "status = status" to display all results in case of no filter
		// Puts Friends only rides at the top thanks to the order by case query
		$query = "SELECT * FROM rides WHERE 
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
				WHEN :status = 'Open' THEN entry_start < NOW() AND entry_end > NOW()
				WHEN :status = 'Closed' THEN entry_start > NOW() OR entry_end < NOW()
				WHEN :status = 'No filter' THEN status = status 
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
			date, meeting_time ASC";
	// Get results total number (without limit)
	$getResultsNumber = $db->prepare($query);
	$getResultsNumber->execute(array(":name" => '%' .$_POST['filter_name']. '%', ":datemin" => $_POST['filter_date_min'], ":datemax" =>  $_POST['filter_date_max'], ":level" => $_POST['filter_level'], ":status" => $_POST['filter_status'], ":friends" => $_POST['filter_friends_only']));
	// Get paginated results
	$result_query = $query .= " LIMIT {$limit} OFFSET {$offset}";
	$getRides = $db->prepare($result_query);
	$getRides->execute(array(":name" => '%' .$_POST['filter_name']. '%', ":datemin" => $_POST['filter_date_min'], ":datemax" =>  $_POST['filter_date_max'], ":level" => $_POST['filter_level'], ":status" => $_POST['filter_status'], ":friends" => $_POST['filter_friends_only']));
	 
?>