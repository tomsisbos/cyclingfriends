<?php

session_start();
require $_SERVER["DOCUMENT_ROOT"] . '/includes/head.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';

if (isset($_POST['bike-id'])) {
	// Lauch uploading to database function if a file has been uploaded
	if (isset($_FILES)) {
		// If this bike have not yet been registered in the bikes table, first create it
		if ($_POST['bike-id'] == 'new') {
			require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
			$bikes = $connected_user->getBikes();
            $new_bike_number = count($bikes) + 1;
            $createBike = $db->prepare('INSERT INTO bikes(user_id, number, type, model, components, wheels, description) VALUES (?, ?, "Other", "", "", "", "")');
            $createBike->execute(array($connected_user->id, $new_bike_number));
			// Get new bike id
            $getBikeId = $db->prepare('SELECT id FROM bikes WHERE user_id = ? AND number = ?');
            $getBikeId->execute(array($connected_user->id, $new_bike_number));
            $new_bike_id = $getBikeId->fetch()[0];
			$_POST['bike-id'] = $new_bike_id;
		}
		$bike = new Bike($_POST['bike-id']);
		$return = $bike->uploadImage();
		if ($return[0]) {
			$_SESSION['successmessage'] = $return[1];
		} else {
			$_SESSION['errormessage'] = $return[1];
		}
	}
}

header('Location: /riders/profile/edit.php');

?>