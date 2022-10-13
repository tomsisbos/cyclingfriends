<?php

if(isset($_POST['delete-bike'])){ // Delete bike if button is clicked
	$successmessage = deleteBike($_POST['delete-bike'], $user['id'])[1];
}
if(isset($_POST['add-bike'])){ // Delete bike if button is clicked
	$successmessage = addBike($_POST['add-bike'], $user['id'])[1];
} /*
if(isset($_POST['deleteBikeButton2'])){ // Delete bike if button is clicked
	$successmessage = deleteBike(2, $user['id'])[1];
}
if(isset($_POST['deleteBikeButton3'])){ // Delete bike if button is clicked
	$successmessage = deleteBike(3, $user['id'])[1];
} */

?>