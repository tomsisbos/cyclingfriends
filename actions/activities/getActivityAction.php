<?php
require '../actions/databaseAction.php';

// Check if an activity id is correctly displayed in the URL
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
$slug = array_slice($url_fragments, -2)[0];
if (is_numeric($slug)) {
	
	// Check if activity exists and if connected user has administration rights
	$checkIfExists = $db->prepare('SELECT * FROM activities WHERE id = ?');
	$checkIfExists->execute(array($slug));
	if ($checkIfExists->rowcount() > 0) {

        $activity = new Activity($slug);

	} else echo 'here1'; //header('location: ' . $connected_user->login . '/activities');

} else echo 'here2'; //header('location: ' . $connected_user->login . '/activities'); ?>