<?php
require '../actions/databaseAction.php';

// Check if an activity id is correctly displayed in the URL
if (isset($_GET['id']) AND !empty($_GET['id'])) {
	
	// Check if activity exists and if connected user has administration rights
	$checkIfExists = $db->prepare('SELECT * FROM activities WHERE id = ?');
	$checkIfExists->execute(array($_GET['id']));
	if ($checkIfExists->rowcount() > 0) {

        $activity = new Activity($_GET['id']);

	} else header('location: myrides.php');

} else header('location: myrides.php'); ?>