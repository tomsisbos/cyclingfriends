<?php
 
	require '../actions/databaseAction.php';
		
	// If no value, set following_search to '%' in order to select all entries
	if (empty($_POST['following_search'])) {
		$_POST['following_search'] = '%';
	}
	
	// If no value or 'approval date' selected, set following_orderby to 'null' in order to keep the approval date default order
	if (empty($_POST['following_orderby']) OR $_POST['following_orderby'] == 'approval_date') {
		$_POST['following_orderby'] = 'null';
	}
		
	// Get connected user followings
	$followings = $connected_user->getFollowingList();
		
	// Get user data of each following id from the database according to filter queries
	$query = 
		"SELECT * FROM users WHERE
				login LIKE '%' :query '%'
			AND
				id IN ('".implode("','",$followings)."')
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
			END
		";
	$getFollowingsData = $db->prepare($query);
	$getFollowingsData->execute(array(":query" => $_POST['following_search'], ":index" => $_POST['following_orderby']));
	
?>