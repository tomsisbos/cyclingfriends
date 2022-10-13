<?php
// Lauch uploading to database function if a file has been uploaded
if(isset($_FILES['propicfile'])){		
	$return = uploadProfilePicture();
	if($return['0'] == false){
		$errormessage = $return['1'];
	}
	else{
		$successmessage = $return['1'];
	}
} 

// Lauch downloading function
$profile_picture = $connected_user->downloadPropic();
?>