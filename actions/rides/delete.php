<?php

require '../actions/users/security.php';
require '../actions/database.php';

if (isset($_GET['id']) AND !empty($_GET['id'])) {
	
	$ride_id = $_GET['id'];
	
	$checkIfRideExists = $db->prepare('SELECT ride_author_id FROM rides WHERE id = ?');
	$checkIfRideExists->execute(array($ride_id));
	
	if ($checkIfRideExists->rowCount() > 0) {
		
		$user_info = $checkIfRideExists->fetch();
		
		if ($_SESSION['id'] == $user_info['ride_author_id']) {
			
			?>
				<script>
					confirm 'Do you really want to delete this ride ?';
				</script>
			<?php 
		
			$deleteThisRide = $db->prepare('DELETE FROM rides WHERE id = ?');
			$deleteThisRide->execute(array($ride_id));
			
			header('Location: /ride/organizations');
		
		} else {
		
			$errormessage = "You don't have admin rights on this ride.";
		
		}
	
	} else {
		
		$errormessage = "No ride of this ID has been found in the database.";
		
	}
	
} else {
	
	$errormessage = "No ride has been found.";

}