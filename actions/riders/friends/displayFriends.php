<?php
 
	require '../actions/database.php';
		
	// If no value, set friend_search to '%' in order to select all entries
	if (empty($_POST['friend_search'])) {
		$_POST['friend_search'] = '%';
	}
	
	// If no value or 'approval date' selected, set friend_orderby to 'null' in order to keep the approval date default order
	if (empty($_POST['friend_orderby']) OR $_POST['friend_orderby'] == 'approval_date') {
		$_POST['friend_orderby'] = 'null';
	}
		
	// Get connected user friends
	$friends = getConnectedUser()->getFriends();
		
	// Get user data of each friend id from the database according to filter queries
	$query = 
		"SELECT id FROM users WHERE
				login LIKE :query
			AND
				id IN ('".implode("','",$friends)."') 
		ORDER BY
			CASE
				WHEN :index = 'level' THEN 
					(CASE 
						WHEN level = 'Athlete' THEN 0
						WHEN level = 'Intermediate' THEN 1
						WHEN level = 'Beginner' THEN 2
						ELSE 3
					END)
				END ASC,
			CASE 
				WHEN :index = 'login' THEN login
				WHEN :index = 'last_name' THEN last_name
				WHEN :index = 'first_name' THEN first_name
				WHEN :index = 'birthdate' THEN birthdate
				ELSE (SELECT NULL)
			END DESC";
	// Get results total number (without limit)
	$getResultsNumber = $db->prepare($query);
	$getResultsNumber->execute(array(":query" => '%' .$_POST['friend_search']. '%', ":index" => $_POST['friend_orderby']));
	// Get paginated results
	$result_query = $query .= " LIMIT {$limit} OFFSET {$offset}";
	$getFriendsData = $db->prepare($result_query);
	$getFriendsData->execute(array(":query" => '%' .$_POST['friend_search']. "%", ":index" => $_POST['friend_orderby']));
	
?>