<?php

	if((isset($_POST['bike-1-save']) OR isset($_POST['bike-2-save']) OR isset($_POST['bike-3-save'])) AND !empty($_POST)){
	
		require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
		
		// Check if bike is set
		$bike = new Bike();
		if ($bike->isSet(getConnectedUser()->id, $_POST['bike-number'])) {
			$bike->update(htmlspecialchars($_POST['bike-type']), htmlspecialchars($_POST['bike-model']), htmlspecialchars($_POST['bike-components']), htmlspecialchars($_POST['bike-wheels']), htmlspecialchars($_POST['bike-description']), $user->id, $_POST['bike-number']);
			$successmessage = 'Bike infos have correctly been updated !';
		} else {
			$bike->create($user->id, $_POST['bike-number'], htmlspecialchars($_POST['bike-type']), htmlspecialchars($_POST['bike-model']), htmlspecialchars($_POST['bike-components']), htmlspecialchars($_POST['bike-wheels']), htmlspecialchars($_POST['bike-description']));
			$successmessage = 'Bike infos have correctly been updated !';
		}

	} ?>