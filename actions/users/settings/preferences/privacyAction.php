<?php
include $_SERVER["DOCUMENT_ROOT"]. '/actions/databaseAction.php';

	// Set settings table values according to submitted data
	if(isset($_POST['privacy-submit'])){
		
		// Set to false as default
		$hide_on_riders     = NULL;
		$hide_on_neighbours = NULL;
		$hide_on_chat       = NULL;
		
		if(isset($_POST['hide_on_riders_only'])){
			$hide_on_riders     = 1;
		}
		if(isset($_POST['hide_on_riders_neighbours'])){
			$hide_on_riders     = 1;
			$hide_on_neighbours = 1;
		}
		if(isset($_POST['hide_on_chat'])){
			$hide_on_chat     = 1;
		}

		// If connected user doesn't have a setting entry yet in settings table, build it
		if(!checkIfUserHasSettingsEntry($_SESSION['id'])){
			$setPrivacy = $db->prepare('INSERT INTO settings(user_id, hide_on_riders, hide_on_neighbours, hide_on_chat) VALUES (?, ?, ?, ?)');
			$setPrivacy->execute(array($_SESSION['id'], $hide_on_riders, $hide_on_neighbours, $hide_on_chat));
			$successmessage = 'Your privacy preferences have been correctly updated !';
			updateSessionSettings();
		
		// Else, update it
		}else{
			$updatePrivacy = $db->prepare('UPDATE settings SET hide_on_riders = ?, hide_on_neighbours = ?, hide_on_chat = ? WHERE user_id = ?');
			$updatePrivacy->execute(array($hide_on_riders, $hide_on_neighbours, $hide_on_chat, $_SESSION['id']));
			$successmessage = 'Your privacy preferences have been correctly updated !';
			updateSessionSettings();
		}
					
	} ?>