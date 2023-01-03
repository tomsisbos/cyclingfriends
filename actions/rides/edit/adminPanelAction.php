<?php

// If admin has clicked on the save button of the admin panel
if (isset($_POST['save'])) {
	
	require '../actions/databaseAction.php';

	if (isset($_POST['privacy'])) {
		$updatePrivacyInfos = $db->prepare('UPDATE rides SET privacy = ? WHERE id = ?');
		$updatePrivacyInfos->execute(array($_POST['privacy'], $ride->id));
	}

	if (isset($_POST['entry_start']) AND !empty($_POST['entry_start'])) {
		$updateEntryStartInfos = $db->prepare('UPDATE rides SET entry_start = ? WHERE id = ?');
		$updateEntryStartInfos->execute(array($_POST['entry_start'], $ride->id));
	}

	if (isset($_POST['entry_end']) AND !empty($_POST['entry_end'])) {
		$updateEntryEndInfos = $db->prepare('UPDATE rides SET entry_end = ? WHERE id = ?');
		$updateEntryEndInfos->execute(array($_POST['entry_end'], $ride->id));
	}
	
		$successmessage = 'Settings have been successfully updated.';

}
	
?>