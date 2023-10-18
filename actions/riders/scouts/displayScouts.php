<?php
 
	require '../actions/database.php';
		
	// If no value, set scout_search to '%' in order to select all entries
	if (empty($_POST['scout_search'])) {
		$_POST['scout_search'] = '%';
	}
	
	// If no value or 'approval date' selected, set scout_orderby to 'null' in order to keep the approval date default order
	if (empty($_POST['scout_orderby']) OR $_POST['scout_orderby'] == 'approval_date') {
		$_POST['scout_orderby'] = 'null';
	}
		
	// Get user data of each scout id from the database according to filter queries
	$query = "SELECT * FROM users WHERE
        login LIKE :query
		AND id IN (
			SELECT followed_id
			FROM followers
			WHERE following_id = :user_id
		)
		ORDER BY CASE
			WHEN :index = 'level' THEN (
				CASE
					WHEN level = 3 THEN 0
					WHEN level = 2 THEN 1
					WHEN level = 1 THEN 2
					ELSE 3
				END
			)
		END ASC,
		CASE
			WHEN :index = 'login' THEN login
			WHEN :index = 'last_name' THEN last_name
			WHEN :index = 'first_name' THEN first_name
			WHEN :index = 'birthdate' THEN birthdate::text
			ELSE (
				SELECT NULL
			)
		END";

		// Get results total number (without limit)
		$getResultsNumber = $db->prepare($query);
		$getResultsNumber->execute(array(":query" => '%' . $_POST['scout_search'] . '%', ":user_id" => getConnectedUser()->id, ":index" => $_POST['scout_orderby']));

		// Bind the user_id separately;

		// Get paginated results
		$result_query = $query .= " LIMIT {$limit} OFFSET {$offset}";
		$getScoutsData = $db->prepare($result_query);

		// Bind the user_id separately for the second query
		$getScoutsData->execute(array(":query" => '%' . $_POST['scout_search'] . '%', ":user_id" => getConnectedUser()->id, ":index" => $_POST['scout_orderby']));
	
?>