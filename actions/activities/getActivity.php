<?php
require '../actions/database.php';

// Check if an activity id is correctly displayed in the URL
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
$slug = array_slice($url_fragments, -2)[0];
if (is_numeric($slug)) {
	
	// Check if activity exists
	$checkIfExists = $db->prepare('SELECT id FROM activities WHERE id = ?');
	$checkIfExists->execute(array($slug));
	if ($checkIfExists->rowcount() > 0) {

        $activity = new Activity($slug);

	} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/myactivities');

} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/myactivities'); ?>