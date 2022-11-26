<?php

if (basename($_SERVER['REQUEST_URI']) == 'join') {
	require '../actions/databaseAction.php';	
	// If connected user has not already joined,
	if (!$ride->isParticipating($connected_user)) {
		// If ride is not already full,
		if (!$ride->isFull()) {
			$ride->join($connected_user);
			$successmessage = 'You have joined ' .$ride->name. ' ! It will sure be great cycling !';		
		} else {		
			$errormessage = $ride->name. 'has already reached the maximum number of participants.';		
		}	
	} else {		
		$errormessage = 'You already joined ' .$ride->name. '.';	
	}	
}

if (basename($_SERVER['REQUEST_URI']) == 'quit') {
	require '../actions/databaseAction.php';	
	// If rider is on the ride,
	if ($ride->isParticipating($connected_user)) {
		$ride->quit($connected_user);
		$successmessage = 'You have left ' .$ride->name. '. You can join it again anytime within entry period if ever you change your mind.';		
	} else {
		$errormessage = 'You already left ' .$ride->name. '.';
	}	
}

?>