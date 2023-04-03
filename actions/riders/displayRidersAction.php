<?php
 
	require '../actions/databaseAction.php';

	$query = "SELECT * FROM users WHERE id NOT IN (SELECT id FROM settings WHERE hide_on_riders = true) ORDER BY id ASC";
	
	// Get results total number (without limit)
	$getResultsNumber = $db->prepare($query);
	$getResultsNumber->execute();

	// Get paginated results
	$result_query = $query .= " LIMIT {$limit} OFFSET {$offset}";
	$getRiders = $db->prepare($result_query);
	$getRiders->execute();
	 
?>