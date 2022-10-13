<?php
require '../actions/databaseAction.php';

// Check if a ride id is correctly displayed in the URL
if (isset($_GET['id']) AND !empty($_GET['id'])) {
	
	// Check if ride exists and if connected user has administration rights
	$checkIfExists = $db->prepare('SELECT * FROM rides WHERE id = ?');
	$checkIfExists->execute(array($_GET['id']));
	if ($checkIfExists->rowcount() > 0) {

		// If first access on edit page, store editable ride infos in session edit-forms variable
		if (!isset($_GET['stage'])) {

			$ride = new Ride($_GET['id']);

			$_SESSION['edit-forms'][1]['ride-name'] = $ride->name;
			$_SESSION['edit-forms'][1]['date'] = $ride->date;
			$_SESSION['edit-forms'][1]['meeting-time'] = $ride->meeting_time;
			$_SESSION['edit-forms'][1]['departure-time'] = $ride->departure_time;
			$_SESSION['edit-forms'][1]['finish-time'] = $ride->finish_time;
			$_SESSION['edit-forms'][1]['nb-riders-min'] = $ride->nb_riders_min;
			$_SESSION['edit-forms'][1]['nb-riders-max'] = $ride->nb_riders_max;
			$_SESSION['edit-forms'][1]['level'] = $ride->getAcceptedLevelsValues();
			$_SESSION['edit-forms'][1]['accepted-bikes'] = $ride->getAcceptedBikesValues();
			$_SESSION['edit-forms'][1]['ride-description'] = $ride->description;
			if (isset($ride->route)) $_SESSION['edit-forms'][2]['method'] = 'draw';
			else $_SESSION['edit-forms'][2]['method'] = 'pick';
			$_SESSION['edit-forms'][2]['meetingplace'] = $ride->meeting_place;
			$_SESSION['edit-forms'][2]['distance-about'] = $ride->distance_about;
			$_SESSION['edit-forms'][2]['distance'] = $ride->distance;
			$_SESSION['edit-forms'][2]['finishplace'] = $ride->finish_place;
			$_SESSION['edit-forms'][2]['terrain'] = $ride->terrain;
			$_SESSION['edit-forms'][2]['course-description'] = $ride->course_description;
			if (round($ride->checkpoints[0]->lngLat->lng, 2) == round($ride->checkpoints[count($ride->checkpoints) - 1]->lngLat->lng, 2) && round($ride->checkpoints[0]->lngLat->lat, 2) == round($ride->checkpoints[count($ride->checkpoints) - 1]->lngLat->lat, 2)) {
				$_SESSION['edit-forms'][2]['options'] = ['sf' => true]; } // If coordinates rounded to 0,02 of the first and of the last road waypoint are equal, then set options SF to true
			else $_SESSION['edit-forms'][2]['options'] = ['sf' => false]; // If route start coordinates equals route end coordinates, set SF options to true, else set to false
			$_SESSION['edit-forms'][2]['checkpoints'] = $ride->checkpoints;
			if (isset($ride->route)) $_SESSION['edit-forms'][2]['route-id'] = $ride->route->id;
			// If not, redirect to my rides pages
			if ($ride->author != $connected_user) header('location: myrides.php');
		}

	} else header('location: myrides.php');

} else header('location: myrides.php'); ?>

