<?php 

if (isset($_POST['profile-infos-save']) and !empty($_POST)) {
	
	// Set undefined values as NULL
	if ($_POST['gender'] == 'Undefined') $_POST['gender'] = NULL;
	if (empty($_POST['birthdate'])) $_POST['birthdate'] = NULL;
	
	// Update users table
	require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
	$updateProfileInfos = $db->prepare('UPDATE users SET last_name = ?, first_name = ?, gender = ?, birthdate = ?, place = ?, level = ?, description = ? WHERE id = ?');
	$updateProfileInfos->execute(array(htmlspecialchars($_POST['last-name']), htmlspecialchars($_POST['first-name']), $_POST['gender'], $_POST['birthdate'], htmlspecialchars($_POST['place']), $_POST['level'], htmlspecialchars($_POST['description']), $user->id));
	
	// Display a message
	$successmessage = 'Profile infos have correctly been updated ! Click <a href="' .getCurrentPageUrl(). '">here</a> to refresh the page and display your changes.';
} ?>