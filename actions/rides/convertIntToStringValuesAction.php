<?php 

if(isset($ride) AND !empty($ride)){
	
// Converts select form int values to string values

	// Terrain
	switch ($ride->terrain) {
		case 4: $terrain_value = 'Mountains'; break;
		case 3: $terrain_value = 'Hills'; break;
		case 2: $terrain_value = 'Small hills'; break;
		case 1: $terrain_value = 'Flat'; break;
		default : $terrain_value = 'unknown'; 
	}

} else if (isset($_SESSION['forms']) AND !empty($_SESSION['forms'])) {

	// Terrain
	switch ($_SESSION['forms']['2']['terrain']) {
		case 4: $terrain_value = 'Mountains'; break;
		case 3: $terrain_value = 'Hills'; break;
		case 2: $terrain_value = 'Small hills'; break;
		case 1: $terrain_value = 'Flat'; break;
		default : $terrain_value = 'unknown'; 
	}

} else if (isset($_SESSION['edit-forms']) AND !empty($_SESSION['edit-forms'])) {
	
	// Terrain
	switch ($_SESSION['edit-forms']['2']['terrain']) {
		case 4: $terrain_value = 'Mountains'; break;
		case 3: $terrain_value = 'Hills'; break;
		case 2: $terrain_value = 'Small hills'; break;
		case 1: $terrain_value = 'Flat'; break;
		default : $terrain_value = 'unknown'; 
	}
	
}else{
	
	$errormessage = 'We have a problem with the file : actions/rides/convertInToStringValuesAction.php';
	
}
?>

