<?php // Lauch uploading from the database function if a file has been uploaded and display returned message
if (isset($_FILES['file'])) {
	$uploadReturn = $ride->uploadGallery();
	// Display returned message
	if (isset($uploadReturn)) {
		if ($uploadReturn[0] != 1) {
			$errormessage = $uploadReturn[1];
		} else {
			$successmessage = $uploadReturn[1];
		}
	}
}
		
// Launch delete function if delete gallery button has been clicked
if (isset($_POST['delete'])) {
	$deleteReturn = $ride->deleteGallery();
	// Display returned message
	if (isset($deleteReturn)) {
		if ($deleteReturn[0] != 1) {
			$errormessage = $deleteReturn[1];
		} else if ($deleteReturn[0] = 1) {
			$successmessage = $deleteReturn[1];
		}
	}
} ?>