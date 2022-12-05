<?php

// Lauch uploading to database function if a file has been uploaded
if (isset($_FILES['propicfile'])) {		
	$message = $connected_user->uploadPropic();
	if (isset($message['error'])) $errormessage = $message['error'];
	else if (isset($message['success'])) $successmessage = $message['success'];
}

// Lauch downloading function
$profile_picture = $connected_user->downloadPropic(); ?>