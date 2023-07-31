<?php
 
require '../actions/database.php';
require_once '../includes/functions.php';

if (isset($_FILES['bikeimagefile'])) {
	
	if (!is_uploaded_file($_FILES['bikeimagefile']['tmp_name'])) {
		return array('error' => "ファイルアップロード中に問題が発生しました。");
			
	} else {

		$bike = new Bike();

		// In case of a new bike, create it before
		if ($_POST['bike-id'] == 'new') $bike->create(getConnectedUser()->id);
		// Else, load existing data
		else $bike->load($_POST['bike-id']);
		
		$result = $bike->uploadImage($_FILES['bikeimagefile']);
		if (isset($result['error'])) $errormessage = $result['error'];
		if (isset($result['success'])) $successmessage = $result['success'];
	}
}

/*
if (isset($_POST['bike-id'])) {
	// Lauch uploading to database function if a file has been uploaded
	if (isset($_FILES)) {
		// If this bike have not yet been registered in the bikes table, first create it
		if ($_POST['bike-id'] == 'new') {
			require $_SERVER["DOCUMENT_ROOT"] . '/actions/database.php';
			$bikes = getConnectedUser()->getBikes();
            $new_bike_number = count($bikes) + 1;
            $createBike = $db->prepare('INSERT INTO bikes(user_id, number, type, model, components, wheels, description) VALUES (?, ?, "Other", "", "", "", "")');
            $createBike->execute(array(getConnectedUser()->id, $new_bike_number));
			// Get new bike id
            $getBikeId = $db->prepare('SELECT id FROM bikes WHERE user_id = ? AND number = ?');
            $getBikeId->execute(array(getConnectedUser()->id, $new_bike_number));
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

header('Location: /riders/profile/edit.php');*/

?>