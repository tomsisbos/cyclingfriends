<?php
 
	require '../actions/database.php';
	
    $checkIfExists = $db->prepare('SELECT id FROM rides WHERE id = ?');
    $checkIfExists->execute([$params['ride_id']]);

	// Redirect to calendar if ride doesn't exist
    if ($checkIfExists->rowCount() > 0) $ride = new Ride($params['ride_id']);
	else header('location: ' .$router->generate('rides-calendar'));

	$basename = basename($_SERVER['REQUEST_URI']);
			
	// If ride admin have submitted data, then replace existing data by submitted one
	if (isset($_POST['save'])) {
		$ride->privacy     = $_POST['privacy'];
		$ride->entry_start = $_POST['entry_start'];
		$ride->entry_end   = $_POST['entry_end'];
	}
?>