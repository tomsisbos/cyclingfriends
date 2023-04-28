<?php
 
	require '../actions/databaseAction.php';

	$query = "SELECT * FROM users ORDER BY id ASC";
	
	// Get results total number (without limit)
	$getResultsNumber = $db->prepare($query);
	$getResultsNumber->execute();

	// Get paginated results
	$result_query = $query .= " LIMIT {$limit} OFFSET {$offset}";
	$getRiders = $db->prepare($result_query);
	$getRiders->execute();
	 
?>