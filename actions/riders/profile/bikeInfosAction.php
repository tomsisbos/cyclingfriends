<?php

	if((isset($_POST['bike-1-save']) OR isset($_POST['bike-2-save']) OR isset($_POST['bike-3-save'])) AND !empty($_POST)){
	
		require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
		
		// Check if bike is set
		if($user->isBike($_POST['bike-number'])){
			$updateBikeInfos = $db->prepare('UPDATE bikes SET bike_type = ?, bike_model = ?, bike_components = ?, bike_wheels = ?, bike_description = ? WHERE user_id = ? AND bike_number = ?');
			$updateBikeInfos->execute(array(htmlspecialchars($_POST['bike-type']), htmlspecialchars($_POST['bike-model']), htmlspecialchars($_POST['bike-components']), htmlspecialchars($_POST['bike-wheels']), htmlspecialchars($_POST['bike-description']), $user->id, $_POST['bike-number']));
			$successmessage = 'Bike infos have correctly been updated !';
			
		}else{
			$insertBikeInfos = $db->prepare('INSERT INTO bikes (user_id, bike_number, bike_type, bike_model, bike_components, bike_wheels, bike_description) VALUES (?, ?, ?, ?, ?, ?, ?)');
			$insertBikeInfos->execute(array($user->id, $_POST['bike-number'], htmlspecialchars($_POST['bike-type']), htmlspecialchars($_POST['bike-model']), htmlspecialchars($_POST['bike-components']), htmlspecialchars($_POST['bike-wheels']), htmlspecialchars($_POST['bike-description'])));
			$successmessage = 'Bike infos have correctly been updated !';
		}

	} ?>