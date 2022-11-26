<?php
 
	require '../actions/databaseAction.php';
	
	// Get data from database
	$getRiders = $db->prepare('SELECT * FROM users WHERE id NOT IN (SELECT user_id FROM settings WHERE hide_on_riders = true) ORDER BY id ASC');
	$getRiders->execute();
	 
?>