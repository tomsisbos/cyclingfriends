<?php

include '../actions/databaseAction.php';

// If user clicks on submit button
if (isset($_POST['validate'])) {
	
	// Sort form infos into array variables
	$ride_infos = $_SESSION['edit-forms'][1];
	$course_infos = $_SESSION['edit-forms'][2];
	
	// If required fields are filled in
	if (!empty($ride_infos['ride-name'])
	AND !empty($ride_infos['date'])
	AND !empty($ride_infos['meeting-time'])
	AND !empty($ride_infos['departure-time'])
	AND !empty($ride_infos['finish-time'])
	AND !empty($ride_infos['nb-riders-min'])
	AND !empty($ride_infos['nb-riders-max'])
	AND !empty($ride_infos['level'])
	AND !empty($ride_infos['accepted-bikes'])
	AND !empty($ride_infos['ride-description'])
	AND !empty($course_infos['meetingplace'])
	AND !empty($course_infos['distance'])
	AND !empty($course_infos['terrain'])
	AND !empty($course_infos['course-description'])) {

		if ($ride_infos['nb-riders-min'] > $ride_infos['nb-riders-max']) $errormessage = 'The minimum number of riders can\'t be higher than the maximum one.';
		else if ($ride_infos['departure-time'] < $ride_infos['meeting-time']) $errormessage = 'Departure time can\'t be set before meeting time.';
		else if (($ride_infos['finish-time'] < $ride_infos['meeting-time']) OR ($ride_infos['finish-time'] < $ride_infos['departure-time'])) $errormessage = 'Finish time can\'t be set before meeting or starting time.';

		else {
	
			// Setting input data into variables
		
			$ride_name = htmlspecialchars($ride_infos['ride-name']);
			$date = $ride_infos['date'];
			$meeting_time = $ride_infos['meeting-time'];
			$departure_time = $ride_infos['departure-time'];
			$finish_time = $ride_infos['finish-time'];
			$nb_riders_min = $ride_infos['nb-riders-min'];
			$nb_riders_max = $ride_infos['nb-riders-max'];
			include '../actions/rides/new/defineLevelFromFormValuesAction.php'; // $beginner, $intermediate, $athlete
			include '../actions/rides/new/defineAcceptedBikesFromFormValuesAction.php'; // $city_bike, $road_bike, $mountain_bike, $gravel_cx_bike
			$ride_description = nl2br(htmlspecialchars($ride_infos['ride-description']));
			$meeting_place = $course_infos['meetingplace']['geolocation']['city']. ' (' .$course_infos['meetingplace']['geolocation']['prefecture']. ')';
			$distance_about = $course_infos['distance-about'];
			$distance = htmlspecialchars($course_infos['distance']);
			$finish_place = $course_infos['finishplace']['geolocation']['city']. ' (' .$course_infos['finishplace']['geolocation']['prefecture']. ')';
			include '../actions/rides/new/defineTerrainFromStringAction.php'; // 'Flat', 'Small hills', 'Hills', 'Mountains'
			$course_description = nl2br(htmlspecialchars($course_infos['course-description']));
			if (isset($course_infos['route-id'])) $route_id = $course_infos['route-id'];
			else $route_id = NULL;
				
			// Other data
			$ride_id = $ride_slug;
		
			// Edit data from 'rides' table
			$edit_ride = $db->prepare('UPDATE rides SET name = ?, date = ?, meeting_time = ?, departure_time = ?, finish_time = ?, nb_riders_min = ?, nb_riders_max = ?, level_beginner = ?, level_intermediate = ?, level_athlete = ?, citybike = ?, roadbike = ?, mountainbike = ?, gravelcxbike = ?, description = ?, meeting_place = ?, distance_about = ?, distance = ?, finish_place = ?, terrain = ?, course_description = ?, route_id = ? WHERE id = ?');
			$edit_ride->execute(array($ride_name, $date, $meeting_time, $departure_time, $finish_time, $nb_riders_min, $nb_riders_max, $level_beginner, $level_intermediate, $level_athlete, $citybike, $roadbike, $mountainbike, $gravelcxbike, $ride_description, $meeting_place, $distance_about, $distance, $finish_place, $terrain, $course_description, $route_id, $ride_id));
			
			// Setting course data into variables
			$checkpoints = $course_infos['checkpoints'];

			if (isset($_SESSION['edit-course']['featuredimage']) AND !empty($_SESSION['edit-course']['featuredimage'])) $featured_image = $_SESSION['edit-course']['featuredimage'];
			else $featured_image = 0;
			// Double meetingpoint at the end if necessary
			if ($course_infos['options']['sf'] === false) {
				$numberOfEntries = count($checkpoints);
			} else {
				$numberOfEntries = count($checkpoints) + 1;
				$checkpoints[$numberOfEntries - 1] = $checkpoints[0];
			}

			// Remove previously stored checkpoints from 'ride_checkpoints' table
			$remove_checkpoints = $db->prepare('DELETE FROM ride_checkpoints WHERE ride_id = ?');
			$remove_checkpoints->execute(array($ride_id));

			for ($i = 0 ; $i < count($checkpoints); $i++) { // count($checkpoints)
				$checkpoint_id = $i;
				$name = 'Checkpoint n°' .$i; $description = ''; $img = NULL; $img_size = NULL; $img_name = NULL; $img_type = NULL;
				if ($i == 0) $name = 'Start';
				if ($i == count($checkpoints) - 1) $name = 'Goal';
				if (isset($checkpoints[$i]['name'])) $name = htmlspecialchars($checkpoints[$i]['name']);
				if (isset($checkpoints[$i]['description'])) $description = htmlspecialchars($checkpoints[$i]['description']);
				// Treatment of images coming from the database
				if (isset($checkpoints[$i]['img']['blob'])) {
					$img = $checkpoints[$i]['img']['blob'];
					$img_size = $checkpoints[$i]['img']['size'];
					$img_name = $checkpoints[$i]['img']['name'];
					$img_type = $checkpoints[$i]['img']['type'];
				// Treatment of uploaded images
				} else if (isset($checkpoints[$i]['img']) AND is_string($checkpoints[$i]['img'])) {
					if (isset($checkpoints[$i]['img'])) $img = base64_decode(base64_encode(mb_substr($checkpoints[$i]['img'], 23)));
					if (isset($checkpoints[$i]['img_size'])) $img_size = $checkpoints[$i]['img_size'];
					if (isset($checkpoints[$i]['img_name'])) $img_name = $checkpoints[$i]['img_name'];
					if (isset($checkpoints[$i]['img_type'])) $img_type = $checkpoints[$i]['img_type'];
				}
				$lng = $checkpoints[$i]['lngLat']['lng'];
				$lat = $checkpoints[$i]['lngLat']['lat'];
				$elevation = $checkpoints[$i]['elevation'];
				if (isset($checkpoints[$i]['distance'])) $distance = $checkpoints[$i]['distance'];
				else $distance = NULL;
				if ($i === 0) {
					$special = 'meetingplace';
					$city = $course_infos['meetingplace']['geolocation']['city'];
					$prefecture = $course_infos['meetingplace']['geolocation']['prefecture'];
				} else if ($i === count($checkpoints) - 1) {
					$special = 'finishplace';
					$city = $course_infos['finishplace']['geolocation']['city'];
					$prefecture = $course_infos['finishplace']['geolocation']['prefecture'];					
				} else {
					$special = '';
					$city = NULL;
					$prefecture = NULL;
				}
				if ($i == $featured_image) $featured = 1; // true
				else $featured = 0; // false
				
				// Insert checkpoints in 'ride_checkpoints' table
				$insert_checkpoints = $db->prepare('INSERT INTO ride_checkpoints(ride_id, checkpoint_id, name, description, img, img_size, img_name, img_type, lng, lat, elevation, distance, special, city, prefecture, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
				$insert_checkpoints->execute(array($ride_id, $checkpoint_id, $name, $description, $img, $img_size, $img_name, $img_type, $lng, $lat, $elevation, $distance, $special, $city, $prefecture, $featured));
			}

			// Unset edit forms data
			unset($_SESSION['edit-forms']);
			unset($_SESSION['edit-course']);

			// Redirect to ride page
			header('location: /ride/' . $ride_id);

		}
				
	} else {
	
		$errormessage = "Please fill in all the required informations before validating";
	
	}
				
	
}

?>