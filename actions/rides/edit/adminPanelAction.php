<?php

// If admin has clicked on the save button of the admin panel
if (isset($_POST['save'])) {
	
	require '../actions/databaseAction.php';

	$ride = new Ride($slug);

	if (isset($_POST['privacy']) AND ($_POST['privacy'] != $ride->privacy)) {
		$updatePrivacyInfos = $db->prepare('UPDATE rides SET privacy = ? WHERE id = ?');
		$updatePrivacyInfos->execute(array($_POST['privacy'], $ride->id));
		foreach ($ride->getParticipants() as $participant_id) $ride->notify($participant_id, 'ride_privacy_change');
	}

	if (isset($_POST['entry_start']) AND !empty($_POST['entry_start']) AND ($_POST['entry_start'] != $ride->entry_start)) {
		$updateEntryStartInfos = $db->prepare('UPDATE rides SET entry_start = ? WHERE id = ?');
		$updateEntryStartInfos->execute(array($_POST['entry_start'], $ride->id));
		foreach ($ride->getParticipants() as $participant_id) $ride->notify($participant_id, 'ride_entry_start_change');
	}

	if (isset($_POST['entry_end']) AND !empty($_POST['entry_end']) AND ($_POST['entry_end'] != $ride->entry_end)) {
		$updateEntryEndInfos = $db->prepare('UPDATE rides SET entry_end = ? WHERE id = ?');
		$updateEntryEndInfos->execute(array($_POST['entry_end'], $ride->id));
		foreach ($ride->getParticipants() as $participant_id) $ride->notify($participant_id, 'ride_entry_end_change');
	}
	
		$successmessage = '変更が保存されました。';

}
	
?>