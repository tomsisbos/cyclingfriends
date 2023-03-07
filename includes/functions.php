<?php

use League\ColorExtractor\Palette;
use League\ColorExtractor\Color;
use League\ColorExtractor\ColorExtractor;

// Get root folder
function root () {
	return substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));	
}

// Check for an AJAX request
function isAjax () {
	return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

// Function for replacing <br /> tags with new lines
function br2nl($input){
	    return preg_replace('/<br\s?\/?>/ius', "\n", str_replace("\n","",str_replace("\r","", htmlspecialchars_decode($input))));
}

// Reset keys of an array to 0,1,2,3...
function reset_keys($array){
	$i = 0;
	$new_array = array();
	foreach ($array as $key => $value){
		$new_array[$i++] = $value;
	}
	return $new_array;
}


// Function for getting current page URL
function getCurrentPageUrl () {
	if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
	{
		$url = "https";
	}
	else
	{
		$url = "http"; 
	}  
	$url .= "://"; 
	$url .= $_SERVER['HTTP_HOST']; 
	$url .= $_SERVER['REQUEST_URI']; 
	return $url; 
}

// Function for checking whether a value is found in a multidimensional array or not
function in_array_r ($needle, $haystack, $strict = false) {
    foreach ($haystack as $item) {
        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
            return true;
        }
    }
    return false;
}

// Function for checking whether a key is found in a multidimensional array or not
function in_array_key_r($needle, $haystack) {

    // is in base array?
    if (array_key_exists($needle, $haystack)) {
        return true;
    }

    // check arrays contained in this array
    foreach ($haystack as $element) {
        if (is_array($element)) {
            if (in_array_key_r($needle, $element)) {
                return true;
            }
        }

    }

    return false;
}

// Update session settings to the latest data
function updateSessionSettings() {
	$_SESSION['settings'] = getConnectedUserSettings();
}



/*
// Function for checking password strength : at least 6 characters
function checkPasswordStrength($password){
	
	// Validate password strength
	$number = preg_match('@[0-9]@', $password);

	if(strlen($password) < 6) {
		return false;
	}else{
		return true;
	}
}

// Function for getting connected user info from user table
function getConnectedUserInfo() {
	require '../actions/databaseAction.php';
	$getUser = $db->prepare('SELECT * FROM users WHERE id = ?');
	$getUser->execute(array($_SESSION['id']));
	return $getUser->fetch();
}

// Function for getting connected user settings from settings table
function getConnectedUserSettings() {
	require '../actions/databaseAction.php';
	$getSettings = $db->prepare('SELECT * FROM settings WHERE user_id = ?');
	$getSettings->execute(array($_SESSION['id']));
	return $getSettings->fetch(PDO::FETCH_ASSOC);
}

// Function for getting connected user rights from users table
function getConnectedUserRights() {
	require '../actions/databaseAction.php';
	$getRights = $db->prepare('SELECT rights FROM users WHERE id = ?');
	$getRights->execute(array($_SESSION['id']));
	return $getRights->fetch(PDO::FETCH_NUM)[0];
}

// Check if there is an entry in settings table matching a specific user ID
function checkIfUserHasSettingsEntry($user_id){
	require '../actions/databaseAction.php';
	
	// Check if the user has already joined the ride
	$checkIfUserHasSettingsEntry = $db->prepare('SELECT user_id FROM settings WHERE user_id = ?');
	$checkIfUserHasSettingsEntry->execute(array($user_id));

	if($checkIfUserHasSettingsEntry->rowCount() > 0){
		return true;
	}else{
		return false;
	}
}

function getUserInfos($user_id){
	require '../actions/databaseAction.php';
	$getUserInfos = $db->prepare('SELECT * FROM users WHERE id = ?');
	$getUserInfos->execute(array($user_id));
	return $getUserInfos->fetch();
}

function getLoginById($user_id){
	require '../actions/databaseAction.php';
	$getLogin = $db->prepare('SELECT login FROM users WHERE id = ?');
	$getLogin->execute(array($user_id));
	return $getLogin->fetch()[0];
}

function getInscriptionDate($user){
	require '../actions/databaseAction.php';
	$getUserInfos = $db->prepare('SELECT * FROM users WHERE id = ?');
	$getUserInfos->execute(array($user));
	return $user_infos = $getUserInfos->fetch();
}

// Get friends list of a specific user
function getFriendsList($friend_id){
	require '../actions/databaseAction.php';
	$getFriends = $db->prepare('SELECT CASE WHEN inviter_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN inviter_id END FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id) AND accepted = 1');
	$getFriends->execute(array(":user_id" => $friend_id));
	return array_column($getFriends->fetchAll(PDO::FETCH_NUM), 0);
}

// Get requesters list of a specific user
function getRequestersList($user_id){
	require '../actions/databaseAction.php';
	// Get all infos about friends of connected user from database in a multidimensionnal array
	$getRequesters = $db->prepare('SELECT inviter_id FROM friends WHERE receiver_id = :user AND accepted = false');
	$getRequesters->execute([":user" => $user_id]);
	$combinedData = $getRequesters->fetchAll();
	// Get requesters ids into a simple array
	$requesters = array();
	for($i = 0; isset($combinedData[$i]); $i++){
		array_push($requesters, $combinedData[$i][0]);
	}
	return $requesters;
}

// Check whether two users are friends or not
function checkIfFriends($friend1, $friend2){
	require '../actions/databaseAction.php';
	$friend1_friendslist = getFriendsList($friend1);
	if(in_array_r($friend2, $friend1_friendslist)){
		return true;
	}else{
		return false;
	}
}

function getInvitationDate($friend_id){
	require '../actions/databaseAction.php';
	$getInvitationDate = $db->prepare('SELECT invitation_date FROM friends WHERE (inviter_id = :user_id OR receiver_id = :user_id)');
	$getInvitationDate->execute(array(":user_id" => $friend_id));
	$invitation_date = $getInvitationDate->fetch();
	return $invitation_date[0];
}

// Function returning an age from a birthdate
function ageFromBirthdate($birthdate){
	$today = date("Y-m-d");
	$diff = date_diff(date_create($birthdate), date_create($today));
	return $diff->format('%y');
}

// Function for downloading users's profile picture
function downloadProfilePicture($user_id){
	
	// Check if there is an image that corresponds to connected user in the database
	require '../actions/databaseAction.php';
	$checkUserId = $db->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
	$checkUserId->execute(array($user_id));
	$checkUserId->fetch();
			
	// If there is one, execute the code
	if($checkUserId->rowCount() > 0){	
		$getImage = $db->prepare('SELECT * FROM profile_pictures WHERE user_id = ?');
		$getImage->execute(array($user_id));
		return $getImage->fetch(PDO::FETCH_ASSOC);	
		
	}else{
		return 'couldn\'t get image data from database.';
	}
}

// Function for downloading & displaying user's profile picture with defined height, width and border-radius attributes
function displaysProfilePictureFreesize($user_id, $height, $width, $borderRadius = "0"){
	$profile_picture = downloadProfilePicture($user_id);
	
	// If the user has uploaded a picture, use it as profile picture
	if(isset($profile_picture['img'])){
		echo '<div style="height: ' .$height. 'px; width: ' .$width. 'px;" class="free-propic-container">';
			echo '<img style="border-radius: ' .$borderRadius. 'px;" class="free-propic-img" src="data:image/jpeg;base64,' .base64_encode($profile_picture['img']). '" />';
		echo '</div>';
		
	// Else, use a profile picture corresponding to user's randomly attribuated icon
	}else{
		require '../actions/databaseAction.php';
		$getImage = $db->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
		$getImage->execute(array($user_id));
		$picture = $getImage->fetch();
		echo '<div style="height: ' .$height. 'px; width: ' .$width. 'px;" class="free-propic-container">';
			echo '<img style="border-radius: ' .$borderRadius. 'px;" class="free-propic-img" src="\includes\media\default-profile-' .$picture['default_profilepicture_id']. '.jpg" />';
		echo '</div>';
	}
}

// Function for downloading & displaying user's profile picture as a rounded icon
function displaysProfilePictureIcon($user_id){
	$profile_picture = downloadProfilePicture($user_id);
	
	echo '<div class="round-propic-container">';
	// If the user has uploaded a picture, use it as profile picture
	if(isset($profile_picture['img'])){
		echo '<img class="round-propic-img" src="data:image/jpeg;base64,' . base64_encode($profile_picture['img']) . '" />';
	// Else, use a profile picture corresponding to user's randomly attribuated icon
	}else{
		$picture = getDefaultProfilePicture($user_id);
		echo '<img class="round-propic-img" src="\includes\media\default-profile-' . $picture['default_profilepicture_id'] . '.jpg" />';
	}
	echo '</div>';
}

// Get bikes infos of a specific user from the bikes table
function getBikes($user_id){
	require '../actions/databaseAction.php';
	// Set the bike variables to NULL in case of no data
	$bike[1] = NULL; $bike[2] = NULL; $bike[3] = NULL;
	// Prepare request of user data
	$getBike1 = $db->prepare('SELECT * FROM bikes WHERE user_id = ? AND bike_number = 1');
	$getBike1->execute(array($user_id));
	// Check if id exists
	if($getBike1->rowcount() > 0){
		// If exists, fetch data into $user_data and display the user infos
		$bike[1] = $getBike1->fetch();
	}	
	// Prepare request of user data
	$getBike2 = $db->prepare('SELECT * FROM bikes WHERE user_id = ? AND bike_number = 2');
	$getBike2->execute(array($user_id));
	// Check if id exists
	if($getBike2->rowcount() > 0){
		// If exists, fetch data into $user_data and display the user infos
		$bike[2] = $getBike2->fetch();
	}
	// Prepare request of user data
	$getBike3 = $db->prepare('SELECT * FROM bikes WHERE user_id = ? AND bike_number = 3');
	$getBike3->execute(array($user_id));
	// Check if id exists
	if($getBike3->rowcount() > 0){
		// If exists, fetch data into $user_data and display the user infos
		$bike[3] = $getBike3->fetch();
	}
	return $bike;
}

// Function for registering a new friendship relation (before validation) in friends table.
function becomeFriends($inviter, $receiver){
	
	// Set variables
	$inviter_id     = $inviter;
	$inviter_login  = getLoginById($inviter);
	$receiver_id    = $receiver;
	$receiver_login = getLoginById($receiver);
	
	require '../actions/databaseAction.php';
	// Check if an entry exists with inviter and receiver id
	$checkIfAlreadySentARequest = $db->prepare('SELECT * FROM friends WHERE (inviter_id = :inviter AND receiver_id = :receiver) OR (inviter_id = :receiver AND receiver_id = :inviter)');
	$checkIfAlreadySentARequest->execute([":inviter" => $inviter_id, ":receiver" => $receiver_id]);
	$friendship = $checkIfAlreadySentARequest->fetch();
	
	// If there is one, return false with an error message depending on if the friends request has already been accepted by receiver or not
	if($checkIfAlreadySentARequest->rowCount() > 0){
		// If accepted is set to true
		if($friendship['accepted']){
			$error = "You already are friend with " .$receiver_login. ".";
			return array(false, $error);
		// If accepted is set to false and current user is the inviter
		}else if($friendship['inviter_id'] == $_SESSION['id']){
			$error = "You already sent an invitation to " .$receiver_login. ".";
			return array(false, $error);
		// else (If accepted is set to false and current user is the receiver)
		}else{
			$error = $receiver_login. ' has already sent you an invitation. You can accept or dismiss it on <a href="/riders/friends.php">your friends page</a>.';
			return array(false, $error);
		}
		
	// If there is no existing entry, insert a new friendship relation (before validation) in friends table, and return true and a success message
	}else{
		$createNewFriendship = $db->prepare('INSERT INTO friends(inviter_id, inviter_login, receiver_id, receiver_login, invitation_date) VALUES (?, ?, ?, ?, ?)');
		$createNewFriendship->execute(array($inviter_id, $inviter_login, $receiver_id, $receiver_login, date('Y-m-d')));
		$success = "Your friends request has been sent to " .$receiver_login. " !";
		return array(true, $success);
	}
}

// Get default profile picture of an user
function getDefaultProfilePicture($user_id){
	require '../actions/databaseAction.php';
	$getImage = $db->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
	$getImage->execute(array($user_id));
	return $getImage->fetch();
}

// Check if a bike is set in the database users table
function checkIfBikeIsSet($user_id, $bike_number = 1){
	// Check for data into users table
	require '../actions/databaseAction.php';
	$checkIfBikeIsSet = $db->prepare('SELECT bike_type FROM bikes WHERE user_id = ? AND bike_number = ?');
	$checkIfBikeIsSet->execute(array($user_id, $bike_number));
	$isSetBike = $checkIfBikeIsSet->fetch();
	// Return true if data has been found, else return false
	if(!empty($isSetBike)){
		return true;
	}else{
		return false;
	}
}

function getFeaturedImage($ride_id){
	require '../actions/databaseAction.php';
	$getFeaturedImage = $db->prepare('SELECT img, img_size, img_name, img_type FROM ride_checkpoints WHERE ride_id = ? AND featured = true');
    $getFeaturedImage->execute(array($ride_id));
    $featuredImage = $getFeaturedImage->fetch(PDO::FETCH_ASSOC);
	return $featuredImage;
}

// Get accepted level list of a specific ride from the database
function getAcceptedLevelTags($ride_id){
	require '../actions/databaseAction.php';
	// Get ride infos
	$getAcceptedLevelInfos = $db->prepare('SELECT level_beginner, level_intermediate, level_athlete FROM rides WHERE id = ?');
	$getAcceptedLevelInfos->execute(array($ride_id));
	$level_list = $getAcceptedLevelInfos->fetch();
	// Set variables to default value
	$i = 0;	$string = '';
	// Build the list string
	foreach($level_list as $level => $boolean){
		// Filter string keys for preventing double iteration
		if(strlen($level)>1){
			// If level is accepted, then write it
			if($boolean == true){
				$string .= '<span class="tag-' .colorLevel(getLevelFromColumnName($level)). '">' .getLevelFromColumnName($level). '</span>';
				$i++;
			}
		}
	}
	
	return $string;
}

// Get accepted level list of a specific ride from the database
function getAcceptedLevelList($ride_id){
	require '../actions/databaseAction.php';
	// Get ride infos
	$getAcceptedLevelInfos = $db->prepare('SELECT level_beginner, level_intermediate, level_athlete FROM rides WHERE id = ?');
	$getAcceptedLevelInfos->execute(array($ride_id));
	$level_list = $getAcceptedLevelInfos->fetch(PDO::FETCH_NUM);
	// If all levels are true, return Anyone
	if($level_list[0] && $level_list[1] && $level_list[2]){
		return 'Anyone';
	}else{
		// Set variables to default value
		$i = 0;	$string = '';
		// Build the list string
		foreach($level_list as $level => $boolean){
			// If level is accepted, then write it
			if($boolean == true){
				// Insert commas between level
				if($i > 0){
					$string .= ', ';
				}
				$string .= getLevelFromKey($level + 1);
				$i++;
			}
		}
	}
	return $string;
}

function defineStatus($ride, $privacy){
	require '../actions/databaseAction.php';
	$substatus = NULL; // Set substatus to NULL for preventing errors in case of no substatus set
	
	// If ride date is passed
	if($ride['date'] < date('Y-m-d')){
		$status = 'Finished';} // status is Finished
	
	// If ride is full
	else if(checkIfRideIsFull($ride['id']) == true){
		$status = 'Full';} // status is Full
		
	// If privacy is set as private
	else if($privacy == 'Private'){
		$status = 'Private';} // status is Private
		
	// If not set as Finished, Full or Private
	else{
		
		// If not set as private, ride date is yet to come and entry start date is yet to come
		if(($privacy != 'Private') AND ($ride['date'] > date('Y-m-d')) AND ($ride['entry_start'] > date('Y-m-d'))){
			$status = 'Closed'; // status is Closed
			$substatus = 'opening soon'; // substatus is opening soon
			}

		// If not set as private, ride date is yet to come and entries are open
		else if(($privacy != 'Private') AND ($ride['date'] > date('Y-m-d')) AND ($ride['entry_start'] <= date('Y-m-d') AND $ride['entry_end'] >= date('Y-m-d'))){
			// If number of applicants is lower than minimum number set
			include '../actions/rides/setParticipationInfosAction.php';
			if($participants_number < $ride['nb_riders_min']){
				$status = 'Open'; // status is Open 
				$substatus = 'riders wanted'; // substatus is riders wanted
			}else{ // If minimum number is reached
				$status = 'Open'; // status is Open
				$substatus = 'ready to depart'; //substatus is ready to depart
			}
		}

		// If not set as private, ride date is yet to come but entries are closed
		else if(($privacy != 'Private') AND ($ride['date'] >= date('Y-m-d')) AND ($ride['entry_start'] < date('Y-m-d') AND $ride['entry_end'] < date('Y-m-d'))){
			$status = 'Closed'; // status is Closed
			$substatus = 'ready to depart'; //substatus is ready to depart
		}

		else{
			$status = 'no status';
		}
		
	}

	$updateStatus = $db->prepare('UPDATE rides SET status = ?, substatus = ? WHERE id = ?');
	$updateStatus->execute(array($status, $substatus, $ride['id']));
	
	return array($status, $substatus);
}

// Check if a ride is full or not
function checkIfRideIsFull($ride_id){
	require '../actions/databaseAction.php';
	
	// Get current number of participants
	$checkParticipation = checkParticipation($ride_id);
	if(!empty($checkParticipation)){
		$current_nb = count($checkParticipation);
	}else{
		$current_nb = 0;
	}
	
	// Get maximum number of participants
	$checkIfRideIsFull = $db->prepare('SELECT nb_riders_max FROM rides WHERE id = ?');
	$checkIfRideIsFull->execute(array($ride_id));
	$ride = $checkIfRideIsFull->fetch();
	$max_nb = $ride['nb_riders_max'];
	
	if($current_nb >= $max_nb){
		return true;
	}else if($current_nb < $max_nb){
		return false;
	}
}

// Function for getting an array with participants list and the total number of them
function checkParticipation($ride_id){
	require '../actions/databaseAction.php';
	$checkParticipation = $db->prepare('SELECT user_id FROM participation WHERE ride_id = ?');
	$checkParticipation->execute(array($ride_id));
	if($checkParticipation->rowCount() > 0){
		// Regroup user ids in one array
		$id_list = array_column($checkParticipation->fetchAll(PDO::FETCH_ASSOC), 'user_id');
		return $id_list;
	}else{
		return NULL;
	}
}

function checkIfParticipate($user_id, $ride_id){
	require '../actions/databaseAction.php';
	
	// Check if the user has already joined the ride
	$checkIfParticipate = $db->prepare('SELECT ride_id FROM participation WHERE user_id = ? AND ride_id = ?');
	$checkIfParticipate->execute(array($user_id, $ride_id));

	if($checkIfParticipate->rowCount() > 0){
		return true;
	}else{
		return false;
	}
}

function checkIfPrivate($ride_id){
	require '../actions/databaseAction.php';
	$checkIfPrivate = $db->prepare('SELECT privacy FROM rides WHERE id = ?');
	$checkIfPrivate->execute(array($ride_id));
	$privacy = $checkIfPrivate->fetch()['privacy'];
	return $privacy;
}

// Check if all participants to a ride are in friends list of an user
function checkIfAllParticipantsAreInFriendsList($user_id, $ride_id){
	$friends_list = getFriendsList($user_id);
	$participants_list = checkParticipation($ride_id);
	if($participants_list){
		$participating_friends = array_intersect($friends_list, $participants_list);
		$participants_not_friends = array_diff(checkParticipation($ride_id), $participating_friends);
		if(count($participants_not_friends) == 0){
			return true;
		}else{
			return false;
		}
	}else{
		return true;
	}
}

function checkIfEntriesAreOpen($ride_id){
	require '../actions/databaseAction.php';
	$entry_period = getEntryPeriod($ride_id);
	
	if(date('Y-m-d') < $entry_period['entry_start']){
		return 'not yet';
	}else if(date('Y-m-d') > $entry_period['entry_end']){
		return 'closed';
	}else if(date('Y-m-d') >= $entry_period['entry_start'] AND date('Y-m-d') <= $entry_period['entry_end']){
		return 'open';
	}else{
		return $errormessage = 'There is a problem in checking the entry period.';
	}
}

function checkIfAcceptedBikesMatches($user_id, $ride_id){
	require '../actions/databaseAction.php';
	
	// Get accepted bikes info
	$getAcceptedBikesInfos = $db->prepare('SELECT citybike, roadbike, mountainbike, gravelcxbike FROM rides WHERE id = ?');
	$getAcceptedBikesInfos->execute(array($ride_id));
	$accepted_bikes = $getAcceptedBikesInfos->fetch(PDO::FETCH_ASSOC);
	
	// Get user bikes info
	$bike = getBikes($user_id);
	
	// Iterates accepted bikes list of the ride
	foreach($accepted_bikes as $biketype => $boolean){
		// For each bike accepted,
		if($boolean){
			// Check if there is a bike type matching in user's bike list
			for($i=1; isset($bike[$i]); $i++){
				if(getBikesFromColumnName($biketype) == $bike[$i]['bike_type']){
					// If there is one, return true
					return true;
				}
			}
		}
	}
	// If no match have been found, return false
	return false;
}

// Function for downloading users's bike image
function downloadBikeImage($user_id, $bike_number){
	
	// Check if there is an image that corresponds to connected user in the database
	require '../actions/databaseAction.php';
	$getImage = $db->prepare('SELECT user_id, bike_number, img, size, name, type FROM bikes WHERE user_id = ? AND bike_number = ?');
	$getImage->execute(array($user_id, $bike_number));
			
	// If there is one, execute the code
	if($getImage->rowCount() > 0){
		return $getImage->fetch();	
		
	// If there is no, return an error message
	}else{
		return 'couldn\'t get image data from database.';
	}
}

// Function for downloading & displaying user's bike image as a presized square
function displayBikeImageSquare($user_id, $bike_number){
	$bike_image = downloadBikeImage($user_id, $bike_number);
	
	// If the user has uploaded an image, use it as bike image
	if(isset($bike_image['img'])){
		echo '<div class="bike-image-container">';
			echo '<img class="bike-image-img" src="data:image/jpeg;base64,' . base64_encode($bike_image['img']) . '" />';
		echo '</div>';
		
	// Else, use a profile picture corresponding to user's randomly attribuated icon
	}else{
		require '../actions/databaseAction.php';
		$getImage = $db->prepare('SELECT default_profilepicture_id FROM users WHERE id = ?');
		$getImage->execute(array($user_id));
		$picture = $getImage->fetch();
		echo '<div class="bike-image-container">';
			echo '<img class="bike-image-img" src="\includes\media\default-bike-' . $picture['default_profilepicture_id'] . '.svg" />';
		echo '</div>';
	}
}

// Function for downloading profile gallery
function downloadProfileGallery($user_id){
	
	// Check if there is an image that corresponds to this user in the database
	require '../actions/databaseAction.php';
	for($i = 0; $i < 5; $i++){
		$getFile = $db->prepare('SELECT img'.$i.' FROM users WHERE id = ? AND img'.$i.' IS NOT NULL');
		$getFile->execute(array($user_id));
		$getFile->fetch();
			
		// If there is one, execute the code
		if($getFile->rowCount() > 0){	
			$getImage = $db->prepare('SELECT img'.$i.', size'.$i.', name'.$i.', type'.$i.' FROM users WHERE id = ?');
			$getImage->execute(array($user_id));
			$img[$i] = $getImage->fetch();
		}
	}
	if(!empty($img)){
		switch(count($img)){
			case 1:
				return array($img[0]);
			case 2:
				return array($img[0], $img[1]);
			case 3:
				return array($img[0], $img[1], $img[2]);
			case 4:
				return array($img[0], $img[1], $img[2], $img[3]);
			case 5:
				return array($img[0], $img[1], $img[2], $img[3], $img[4]);
		}
	}else{
			return NULL;
	}
}

// Function for uploading profile gallery
function uploadProfileGallery(){
	
	// Declaration of variables
	$return     = false;
	$ride_id    = $_GET['id'];
	$img_blob   = '';
	$img_size   = 0;
	$max_size   = 500000;
	$img_name   = '';
	$img_type   = '';
						
	// Count files and start the loop if there are from 1 to 5 files
	require '../actions/databaseAction.php';
	$countfiles = count($_FILES['file']['name']);
	if($countfiles > 5){
		$error = 'You can\'t upload more than 5 files. Please try again with 5 files or less.';
		return array(false, $error);
	}else if($countfiles <= 0 OR empty($_FILES['file']['name'][0])){
		return;
	}else if($countfiles <= 5 AND $countfiles > 0){
		for($i = 0; $i < $countfiles; $i++){
					        
			// Displays an error message if any problem through upload
			$return   = is_uploaded_file($_FILES['file']['tmp_name'][$i]);
			if (!$return) {
				$error = 'Upload problem for ' . $_FILES['file']['name'][$i] . '. Please try again.';
				return array(false, $error);
			}else{
				
			// Sort upload data into variables
			$img_name = $_FILES['file']['name'][$i];
			$img_type = $_FILES['file']['type'][$i];
			$img_blob = file_get_contents($_FILES['file']['tmp_name'][$i]);
					
				// Displays an error message if file size exceeds $max_size
				$img_size = $_FILES['file']['size'][$i];
				if ($img_size > $max_size) {
					$error = $img_name . ' exceeds size limit (500kb). Please reduce the size and try again.';
					return array(false, $error);
							
				}else{					
					$insertImage = $db->prepare('UPDATE users SET img'.$i.' = ?, size'.$i.' = ?, name'.$i.' = ?, type'.$i.' = ? WHERE id = ?');
					$insertImage->execute(array($img_blob, $img_size, $img_name, $img_type, $_GET['id']));
					$checksuccess = true;
				}
			}
        }
		
		if($checksuccess == true){
			$success = $countfiles . ' pictures have correctly been uploaded ! Click <a href="' .getCurrentPageUrl(). '">here</a> to refresh the page and display your changes.';
			return array(true, $success);
		}
    }
}

// Function for deleting profile gallery
function deleteProfileGallery(){
	$user_id = $_GET['id'];
	require '../actions/databaseAction.php';
	$CheckIfGallerySet = $db->prepare('SELECT img0 FROM users WHERE id = ? AND img0 IS NOT NULL');
	$CheckIfGallerySet->execute(array($user_id));
	if($CheckIfGallerySet->rowCount() > 0){	
		$deleteGallery = $db->prepare('UPDATE users SET img0 = NULL, size0 = NULL, name0 = NULL, type0 = NULL, img1 = NULL, size1 = NULL, name1 = NULL, type1 = NULL, img2 = NULL, size2 = NULL, name2 = NULL, type2 = NULL, img3 = NULL, size3 = NULL, name3 = NULL, type3 = NULL, img4 = NULL, size4 = NULL, name4 = NULL, type4 = NULL, caption0 = NULL, caption1 = NULL, caption2 = NULL, caption3 = NULL, caption4 = NULL WHERE id = ?');
		$deleteGallery->execute(array($user_id));
		$success = 'Current gallery has been successfully deleted. Click <a href="' .getCurrentPageUrl(). '">here</a> to refresh the page and display your changes.';
		return array(true, $success);
	}else{
		$error = 'You don\'t have set any gallery yet.';
		return array(false, $error);
	}
}

// Function for uploading a bike image
function uploadBikeImage($bike_number){
	
	// Declaration of variables
	$bikeimagefile = 'bike' .$bike_number. 'imagefile';
	$return        = false;
	$user_id       = $_SESSION['id'];
    $img_blob      = '';
    $img_size      = 0;
    $max_size      = 500000;
	$img_name      = '';
	$img_type      = '';
    $return        = is_uploaded_file($_FILES[$bikeimagefile]['tmp_name']);
        
	// Displays an error message if any problem through upload
    if (!$return) {
		$error = "A problem has occured during file upload.";
		return array(false, $error);
			
	} else {
			
		// Displays an error message if file size exceeds $max_size
		$img_size = $_FILES[$bikeimagefile]['size'];
		if ($img_size > $max_size) {
			$error = 'The image you uploaded exceeds size limit (500kb). Please reduce the size and try again.';
			return array(false, $error);
		}
		
		// Displays an error message if format is not accepted
		$img_type = $_FILES[$bikeimagefile]['type'];
		if ($img_type != 'image/jpeg') {
			$error = 'The file you uploaded is not at *.jpg format. Please try again with a *.jpg image file.';
			return array(false, $error);
		}
			
		// Sort upload data into variables
		$img_name = $_FILES[$bikeimagefile]['name'];
		$img_blob = file_get_contents($_FILES[$bikeimagefile]['tmp_name']);
						
		// Check if connected user has already uploaded a picture
		require '../actions/databaseAction.php';
		$checkUserId = $db->prepare('SELECT user_id FROM bikes WHERE user_id = ? AND bike_number = ?');
		$checkUserId->execute(array($_SESSION['id'], $bike_number));
			
		// If he does, update data in the database
		if($checkUserId->rowCount() > 0){
			$updateImage = $db->prepare('UPDATE bikes SET img = ?, size = ?, name = ?, type = ? WHERE user_id = ? AND bike_number = ?');
			$updateImage->execute(array($img_blob, $img_size, $img_name, $img_type, $user_id, $bike_number));
				
		// If he doesn't, insert a new line into the database
		}else{
			$insertImage = $db->prepare('INSERT INTO bikes(user_id, bike_number, img, size, name, type) VALUES (?, ?, ?, ?, ?, ?)');
			$insertImage->execute(array($user_id, $bike_number, $img_blob, $img_size, $img_name, $img_type));
		}
		
		$success = 'Bike image has correctly been updated !';		
		return array(true, $success);
	}
}

// Get all messages between two users
function getConversation($user1, $user2){
	require '../actions/databaseAction.php';
	$getConversation = $db->prepare('SELECT * FROM messages WHERE sender_id = :user1 AND receiver_id = :user2 UNION SELECT * FROM messages WHERE sender_id = :user2 AND receiver_id = :user1 ORDER BY id');
	$getConversation->execute(array(":user1" => $user1, ":user2" => $user2));
	return $getConversation->fetchAll(PDO::FETCH_ASSOC);
}

function getExistingConversationsUsersList ($user_id) {
	require '../actions/databaseAction.php';
	$getExistingConversations = $db->prepare('SELECT DISTINCT CASE WHEN sender_id = :user_id THEN receiver_id WHEN receiver_id = :user_id THEN sender_id END FROM messages WHERE (sender_id = :user_id OR receiver_id = :user_id) ORDER BY id DESC');
	$getExistingConversations->execute([':user_id' => $user_id]);
	return array_column($getExistingConversations->fetchAll(PDO::FETCH_NUM), 0);
}

// Insert a new message in the message table
function addMessage($receiver_id, $message){
	require '../actions/databaseAction.php';
	
	$sender_id      = $_SESSION['id'];
	$sender_login   = $_SESSION['login'];
	$receiver_login = getLoginById($receiver_id);
	$time           = date('Y-m-d H:i:s');
	
	$addMessage = $db->prepare('INSERT INTO messages (sender_id, sender_login, receiver_id, receiver_login, message, time) VALUES (?, ?, ?, ?, ?, ?)');
	$addMessage->execute(array($sender_id, $sender_login, $receiver_id, $receiver_login, $message, $time));
}

// Function for uploading a profile picture
function uploadProfilePicture(){
	
	// Declaration of variables
	$return     = false;
	$user_id    = $_SESSION['id'];
    $img_blob   = '';
    $img_size   = 0;
    $max_size   = 500000;
	$img_name   = '';
	$img_type   = '';
    $return     = is_uploaded_file($_FILES['propicfile']['tmp_name']);
        
	// Displays an error message if any problem through upload
    if (!$return) {
		$error = "A problem has occured during file upload.";
		return array(false, $error);
			
	} else {
			
		// Displays an error message if file size exceeds $max_size
		$img_size = $_FILES['propicfile']['size'];
		if ($img_size > $max_size) {
			$error = 'The image you uploaded exceeds size limit (500kb). Please reduce the size and try again.';
			return array(false, $error);
		}
		
		// Displays an error message if format is not accepted
		$img_type = $_FILES['propicfile']['type'];
		if ($img_type != 'image/jpeg') {
			$error = 'The file you uploaded is not at *.jpg format. Please try again with a *.jpg image file.';
			return array(false, $error);
		}
			
		// Sort upload data into variables
		$img_name = $_FILES['propicfile']['name'];
		$img_blob = file_get_contents($_FILES['propicfile']['tmp_name']);
						
		// Check if connected user has already uploaded a picture
		require '../actions/databaseAction.php';
		$checkUserId = $db->prepare('SELECT user_id FROM profile_pictures WHERE user_id = ?');
		$checkUserId->execute(array($_SESSION['id']));
			
		// If he does, update data in the database
		if($checkUserId->rowCount() > 0){
			$updateImage = $db->prepare('UPDATE profile_pictures SET img = ?, size = ?, name = ?, type = ? WHERE user_id = ?');
			$updateImage->execute(array($img_blob, $img_size, $img_name, $img_type, $user_id));
				
		// If he doesn't, insert a new line into the database
		}else{
			$insertImage = $db->prepare('INSERT INTO profile_pictures(user_id, img, size, name, type) VALUES (?, ?, ?, ?, ?)');
			$insertImage->execute(array($user_id, $img_blob, $img_size, $img_name, $img_type));
		}
			
		$success = 'Profile picture has correctly been updated !';		
		return array(true, $success);
	}
}

// Function for finding ride ID from ride name
function getRideIdFromRideName ($ride_name) {
	require '../actions/databaseAction.php';
	$getRideId = $db->prepare('SELECT id FROM rides WHERE name = ?');
	$getRideId->execute(array($ride_name));
	$result = $getRideId->fetch(PDO::FETCH_ASSOC);
	return $result['id'];
}

// Function for getting ride privacy and ride status into an array
function getPrivacyAndStatus($ride_id){
	require '../actions/databaseAction.php';
	$getPrivacyStatus = $db->prepare('SELECT privacy, status, substatus FROM rides WHERE id = ?');
	$getPrivacyStatus->execute(array($ride_id));
	$privacyStatus = $getPrivacyStatus->fetch();
	if($getPrivacyStatus->rowCount() > 0){
		return $privacyStatus;
	}else{
		return false;
	}
}*/

function getPrivacyString ($privacy) {
	switch ($privacy) {
		case 'public': return '公開';
		case 'friends_only': return '友達のみ';
		case 'private': return '非公開';
	}
}

// Check if ride name is already set in the database
function checkIfRideIsAlreadySet($ride_name) {
	require '../actions/databaseAction.php';
	// Get all ride_names from the database
	$getRideInfos = $db->prepare('SELECT name FROM rides');
	$getRideInfos->execute();
	// Compare every ride name to the ride name parameter and returns true if finds the same, else return false after the loop
	while ($currentRideTable = $getRideInfos->fetch()) {
		if ($currentRideTable['name'] == $ride_name) {
			return true;
		}
	}
	return false;
}

// Build a level list made of array values separated by commas
function levelFromArray($array){
	$i = 0; $string = '';
	foreach($array as $key => $value){
		if($value){
			// Insert commas between level
			if($i > 0){
				$string .= ', ';
			}
			$string .= getLevelFromKey($value);
			$i++;
		}
	}
	return $string;
}

/*
// Build a level list made of array values separated by commas from an array using booleans
function levelFromArrayBoolean($array){
	$i = 0; $string = '';
	foreach ($array as $key => $boolean) {
		if ($boolean == true) {
			// Insert commas between level
			if ($i > 0) {
				$string .= ', ';
			}
			$string .= getLevelFromKey($key + 1);
			$i++;
		}
	}
	return $string;
}
*/

// Change the level key number to the proper level name
function getLevelFromKey ($key) {
	if ($key == 0) return '誰でも可';
	if ($key == 1) return '初心者';
	if ($key == 2) return '中級者';
	if ($key == 3) return '上級者';
}

// Change the level column name to the proper level name
function getLevelFromColumnName ($level) {
	if ($level == 'level_beginner') return '初心者';
	if ($level == 'level_intermediate') return '中級者';
	if ($level == 'level_athlete') return '上級者';
}

// Build a bikes list made of array values separated by commas
function bikesFromArray ($array) {
	$i = 0; $string = '';
	foreach ($array as $key => $value) {
		if ($value) {
			// Insert commas between level
			if ($i > 0) {
				$string .= ', ';
			}
			$string .= getBikeFromKey($value);
			$i++;
		}
	}
	return $string;
}

/*
// Build a bikes list made of array values separated by commas from an array using booleans
function bikesFromArrayBoolean ($array) {
	if ($array[0] && $array[1] && $array[2] && $array[3]) return 'All bikes accepted';
	else {
		// Set variables to default value
		$i = 0;	$string = '';
		// Build the list string
		foreach ($array as $bike => $boolean) {
			// Filter string keys for preventing double iteration
			if (strlen($bike) > 1) {
				// If bike type is accepted, then write it
				if ($boolean == true) {
					// Insert commas between bike types
					if ($i > 0) $string .= ', ';
					$string .= getBikesFromColumnName($bike);
					// add plural
					$string .= 's';
					$i++;
				}
			}
		}
		return $string;
	}
	$i = 0; $string = '';
	foreach ($array as $key => $value) {
		if ($value) {
			// Insert commas between level
			if ($i > 0) {
				$string .= ', ';
			}
			$string .= getBikeFromKey($value);
			$i++;
		}
	}
	return $string;
}
*/

// Change the bike key name to the proper bike type name
function getBikeFromKey ($key) {
	switch ($key) {
		case 0: return '車種問わず';
		case 1: return 'ママチャリ＆その他の自転車';
		case 2: return 'ロードバイク';
		case 3: return 'マウンテンバイク';
		case 4: return 'グラベル＆シクロクロスバイク';
	}
}

// Change the bike column name to the proper bike name
function getBikesFromColumnName($bike) {
	switch ($bike) {
		case 'citybike': return 'ママチャリ＆その他の自転車';
		case 'roadbike': return 'ロードバイク';
		case 'mountainbike': return 'マウンテンバイク';
		case 'gravelcxbike': return 'グラベル＆シクロクロスバイク';
	}
}

// Delete a bike from the users table
function deleteBike ($bike_number, $user_id) {
	require '../actions/databaseAction.php';
	if (checkIfBikeIsSet($bike_number, $user_id)) {
		$deleteBike = $db->prepare('DELETE FROM bikes WHERE user_id = ? AND bike_number = ?');
		$deleteBike->execute(array($user_id, $bike_number));
		$success = 'バイクが削除しました。';
		return array(true, $success);
	}
}

// Add a bike into the users table
function addBike ($bike_number, $user_id) {
	require '../actions/databaseAction.php';
	if (!checkIfBikeIsSet($bike_number, $user_id)) {
		$addBike = $db->prepare('INSERT INTO bikes (user_id, bike_number) VALUES (?, ?)');
		$addBike->execute(array($user_id, $bike_number));
		$success = 'バイクが追加されました。';
		return array(true, $success);
	}
}
/*
// Attribute a color depending on the status
function colorStatus($status) {
	switch ($status)
	{
		case 'Private' : // red
			$status_color = '#ffbbbb';
			$background_color = '#ff5555'; 
			break;
		case 'Closed' : // blue
			$status_color = '#bbbbff';
			$background_color = '#5555ff'; 
			break;
		case 'Open' : // green
			$status_color = '#afffaa';
			$background_color = '#00e06e';
			break;
		case 'Full' : // blue
			$status_color = '#bbbbff';
			$background_color = '#5555ff'; 
			break;
		case 'Finished' : // red
			$status_color = '#ffbbbb';
			$background_color = '#ff5555'; 
			break;
		default :
			$status_color = 'black';
			$background_color = 'ffffff00'; 
	}
	// Returning the color
	return array($status_color, $background_color);
}*/

// Get the gender of an user and return it as an icon
function getGenderAsIcon($user_id){
	require '../actions/databaseAction.php';
	$getGender = $db->prepare('SELECT gender FROM users WHERE id = ?');
	$getGender->execute(array($user_id));
	$gender = $getGender->fetch();
	if($gender = 'Man'){
		return '<span class="iconify" style="color: #00adff;" data-icon="el:male" data-width="20" data-height="20"></span>';
	}else if($gender = 'Woman'){
		return '<span class="iconify" style="color: #ff6666;" data-icon="el:female" data-width="20" data-height="20"></span>';
	}
	
}

// Function for downloading & displaying profile gallery
function displayProfileGallery($user_id){
	$profile_gallery = downloadProfileGallery($user_id);
	
	if(isset($profile_gallery[0]['img0'])){
		echo '<div class="square-gallery-container margin-bottom">';
			echo '<img class="gallery-picture" src="data:image/jpeg;base64,' . base64_encode($profile_gallery[0]['img0']) . '" />';
		echo '</div>';
	}
	if(isset($profile_gallery[1]['img1'])){
		echo '<div class="square-gallery-container margin-bottom">';
			echo '<img class="gallery-picture" src="data:image/jpeg;base64,' . base64_encode($profile_gallery[1]['img1']) . '" />';
		echo '</div>';
	}
	if(isset($profile_gallery[2]['img2'])){
		echo '<div class="square-gallery-container margin-bottom">';
			echo '<img class="gallery-picture" src="data:image/jpeg;base64,' . base64_encode($profile_gallery[2]['img2']) . '" />';
		echo '</div>';
	}
	if(isset($profile_gallery[3]['img3'])){
		echo '<div class="square-gallery-container margin-bottom">';
			echo '<img class="gallery-picture" src="data:image/jpeg;base64,' . base64_encode($profile_gallery[3]['img3']) . '" />';
		echo '</div>';
	}
	if(isset($profile_gallery[4]['img4'])){
		echo '<div class="square-gallery-container margin-bottom">';
			echo '<img class="gallery-picture" src="data:image/jpeg;base64,' . base64_encode($profile_gallery[4]['img4']) . '" />';
		echo '</div>';
	}
}

// Function for calculating the number of remaining days to a certain date
function nbDaysLeftToDate($date) {
	$currentdate = time();
	$daysleft = (strtotime($date) / 86400) - ($currentdate / 86400);
	return ceil($daysleft);
} /*

// Function for truncating a sentence (string) starting from a certain character number (offset) to a certain number of characters (length)	
function truncate($string, $offset, $length) {
	$string_truncated = substr($string, $offset, $length);
	if(strlen($string) <= $length) return $string_truncated;
	else if(strlen($string) > $length) return $string_truncated. '...';
	else return false;
}*/

// Get a list of users having the 'hide_on_chat' settings option activated
function getUsersDisablingPublicChat() {
	require '../actions/databaseAction.php';
	$getUsersDisablingPublicChat = $db->prepare('SELECT user_id FROM settings WHERE hide_on_chat = 1');
	$getUsersDisablingPublicChat->execute();
	return array_column($getUsersDisablingPublicChat->fetchAll(PDO::FETCH_NUM), 0);
}

// Get the period (early/mid/late + month) from a date
function getPeriod($date) {
	// Get part of the month from the day
	$day = date("d", strtotime($date));
	if ($day < 10) $third = "上旬";
	else if (($day >= 10) AND ($day <= 20)) $third = "中旬";
	else if ($day > 20) $third = "下旬";

	// Get month in letters
	switch (date("n", strtotime($date))) {
		case 1: $month = "1月"; break;
		case 2: $month = "2月"; break;
		case 3: $month = "3月"; break;
		case 4: $month = "4月"; break;
		case 5: $month = "5月"; break;
		case 6: $month = "6月"; break;
		case 7: $month = "7月"; break;
		case 8: $month = "8月"; break;
		case 9: $month = "9月"; break;
		case 10: $month = "10月"; break;
		case 11: $month = "11月"; break;
		case 12: $month = "12月"; 
	}

	return $month . $third;
}

// Scale image file and save it as a blob
function scaleImageFileToBlob($file, $max_width, $max_height) {

    $source_pic = $file;

    list($width, $height, $image_type) = getimagesize($file);

    switch ($image_type)
    {
        case 1: $src = imagecreatefromgif($file); break;
        case 2: $src = imagecreatefromjpeg($file);  break;
        case 3: $src = imagecreatefrompng($file); break;
        default: return '';  break;
    }

    $x_ratio = $max_width / $width;
    $y_ratio = $max_height / $height;

    if( ($width <= $max_width) && ($height <= $max_height) ){
        $tn_width = $width;
        $tn_height = $height;
        }elseif (($x_ratio * $height) < $max_height){
            $tn_height = ceil($x_ratio * $height);
            $tn_width = $max_width;
        }else{
            $tn_width = ceil($y_ratio * $width);
            $tn_height = $max_height;
    }

    $tmp = imagecreatetruecolor($tn_width,$tn_height);

    /* Check if this image is PNG or GIF, then set if Transparent*/
    if(($image_type == 1) OR ($image_type==3))
    {
        imagealphablending($tmp, false);
        imagesavealpha($tmp,true);
        $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
        imagefilledrectangle($tmp, 0, 0, $tn_width, $tn_height, $transparent);
    }
    imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

    /*
     * imageXXX() only has two options, save as a file, or send to the browser.
     * It does not provide you the oppurtunity to manipulate the final GIF/JPG/PNG file stream
     * So I start the output buffering, use imageXXX() to output the data stream to the browser,
     * get the contents of the stream, and use clean to silently discard the buffered contents.
     */
    ob_start();

    switch ($image_type)
    {
        case 1: imagegif($tmp); break;
        case 2: imagejpeg($tmp, NULL, 100);  break; // best quality
        case 3: imagepng($tmp, NULL, 0); break; // no compression
        default: echo ''; break;
    }

    $final_image = ob_get_contents();

    ob_end_clean();

    return $final_image;
}

// Adds treating of orientation to GD original 'imagecreatefromjpeg' function 
function imagecreatefromjpegexif($filename){
	$img = imagecreatefromjpeg($filename);
	$exif = exif_read_data($filename);
	if ($img && $exif && isset($exif['Orientation']))
	{
		$ort = $exif['Orientation'];

		if ($ort == 6 || $ort == 5)
			$img = imagerotate($img, 270, null);
		if ($ort == 3 || $ort == 4)
			$img = imagerotate($img, 180, null);
		if ($ort == 8 || $ort == 7)
			$img = imagerotate($img, 90, null);

		if ($ort == 5 || $ort == 4 || $ort == 7)
			imageflip($img, IMG_FLIP_HORIZONTAL);
	}
	return $img;
}

/*
// Compress an image
function img_compress ($filename, $filesize) {

	// Catch warnings if necessary
	set_error_handler(function ($errno, $errstr, $errfile, $errline) {
		// error was suppressed with the @-operator
		if (0 === error_reporting()) {
			return false;
		}
		throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
	} );

	try {
		// Get extension from file name
		$ext = strtolower(substr($filename, -3));
		$img_name = 'img.' .$ext; // Set image name
		$temp = $_SERVER["DOCUMENT_ROOT"]. '/includes/media/profile/gallery/temp/' .$img_name; // Set temp path
		// Temporary upload raw file on the server
		move_uploaded_file($filename, $temp);
		// Get the file into $img thanks to imagecreatefromjpeg
		$img = imagecreatefromjpegexif($temp);
		if (imagesx($img) > 1600) {
			$img = imagescale($img, 1600); // Only scale if img is wider than 1600px
		}

		// Correct image gamma and contrast
		imagegammacorrect($img, 1.0, 1.1);
		imagefilter($img, IMG_FILTER_CONTRAST, -5);
	
		// Compress it and move it into a new folder
		$path = $_SERVER["DOCUMENT_ROOT"]. '/includes/media/profile/gallery/' .$img_name; // Set path variable
		if ($filesize > 3000000) { // If uploaded file size exceeds 3Mb, set new quality to 75
			imagejpeg($img, $path, 75);
		} else { // If uploaded file size is between 1Mb and 3Mb set new quality to 90
			imagejpeg($img, $path, 90); 
		}
		// Get variable ready
		$img_blob = file_get_contents($path);

	} catch (ErrorException $e) {
		return array(false, $e->getMessage());
	}

	return array(true, $img_blob);
}
*/

// Display success or error message if one is set
function displayMessage () {
	// If is set inside session variable
	if (isset($_SESSION['errormessage'])) {
		$errormessage = $_SESSION['errormessage'];
		unset($_SESSION['errormessage']);
	} else if (isset($_SESSION['successmessage'])) {
		$successmessage = $_SESSION['successmessage'];
		unset($_SESSION['successmessage']);
	}
	// if is set inside direct variable
	if (isset($errormessage)) echo '<div class="error-block" style="margin: 0px;"><p class="error-message">' .$errormessage. '</p></div>';
	if (isset($successmessage)) echo '<div class="success-block" style="margin: 0px;"><p class="success-message">' .$successmessage. '</p></div>';

}

function setPopularity ($rating, $grades_number) {
    // Set ratingScore
    if ($rating == null) { // If no rating data, set default to 3
        $rating = 3;
    } else {
        $rating = intval($rating);
    }
    $ratingScore = $rating * 10;

    // Set numberScore
    if ($grades_number == 0) { // If no grade, 
        $numberScore = 1;
    } else {
        $grades_number = intval($grades_number);
        $numberScore = log($grades_number, 6) + 2;
    }

    // Set popularity
    $popularity = $ratingScore * $numberScore;

    return $popularity;
}

// Check if data is a base 64 encoded string or not
function is_base64_encoded ($data) {
    if (preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $data)) {
       return true;
    } else {
       return false;
    }
};

// Check if $file extension if included in $extensions list
function checkFileExtension ($extensions, $file) {
	$file_parts = pathinfo($file);
	// If file extension is included in $extensions list, return it
	foreach ($extensions as $ext) {
		if ($file_parts['extension'] == $ext) {
			return $ext;
		}
	}
	// Else, return false
	return false;
}

function base64_to_jpeg ($base64_string, $output_file) {
    // Open the output file for writing
    $ifp = fopen($output_file, 'wb'); 

    // Split the string on commas
    // $data[ 0 ] == "data:image/png;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode(',', $base64_string);

    // We could add validation here with ensuring count( $data ) > 1
    fwrite($ifp, base64_decode($data[1]));

    // Clean up the file resource
    fclose($ifp); 

    return $output_file; 
}

function getStars ($number) {
	$string = '';
	for ($i = 0; $i < $number; $i++) {
		$string .= '<div class="d-inline selected-star">★</div>';
	}
	return $string;
}

function getMainColor ($image) {
	// Get content from image regardless from whether an url or a base 64 string
	if (filter_var($image, FILTER_VALIDATE_URL)) $content = imagecreatefromjpeg($image);
	else if (substr($image, 0, 6) == '/media') return '#eeeeee';
	else $content = imagecreatefromstring(base64_decode($image));
	// Get main color from content
	if (imagesx($content) > 200) {
		$image = imagescale($content, 50);
		$palette = Palette::fromGD($image);
	} else $palette = Palette::fromContents(base64_decode($image));
	$extractor = new ColorExtractor($palette);
	$colors = $extractor->extract(1);
	return Color::fromIntToHex($colors[0]);
}

function luminanceLight($hexcolor, $percent) {
	if ( strlen( $hexcolor ) < 6 ) {
		$hexcolor = $hexcolor[0] . $hexcolor[0] . $hexcolor[1] . $hexcolor[1] . $hexcolor[2] . $hexcolor[2];
	}
	$hexcolor = array_map('hexdec', str_split( str_pad( str_replace('#', '', $hexcolor), 6, '0' ), 2 ) );

	foreach ($hexcolor as $i => $color) {
		$from = $percent < 0 ? 0 : $color;
		$to = $percent < 0 ? $color : 255;
		$pvalue = ceil( ($to - $from) * $percent );
		$hexcolor[$i] = str_pad( dechex($color + $pvalue), 2, '0', STR_PAD_LEFT);
	}

	return '#' . implode($hexcolor);
}

function setFilename ($prefix) {
	return $prefix . '_' . rand(0, 999999999999) . '.jpg';
}

function getNextAutoIncrement ($table_name) {
	require root(). '/actions/databaseAction.php';
    $getTableStatus = $db->prepare("SHOW TABLE STATUS LIKE '{$table_name}'");
    $getTableStatus->execute();
    return $getTableStatus->fetchAll(PDO::FETCH_ASSOC)[0]['Auto_increment'];
}

function exists ($table, $id) {
	require root(). '/actions/databaseAction.php';
    $checkIfExists = $db->prepare("SELECT id FROM {$table} WHERE id = ?");
    $checkIfExists->execute(array($id));
	if ($checkIfExists->rowCount() > 0) return true;
	else return false;
} ?>