<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php';
?>

<link rel="stylesheet" href="/assets/css/rides.css" />

<body>

	<?php include 'includes/navbar.php'; ?>
	
	<div class="main">
	
		<h2 class="top-title">Public Rides</h2>
		
		<div class="container end">
		
			<?php // Filter options
			include 'includes/rides/filter-options.php'; 
			
			// Select rides from database according to filter queries
			include 'actions/rides/displayAction.php'; 
			
			// Display ride cards
			include 'includes/rides/display-rides.php';
			
			// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
			if (!isset($is_ride)) $errormessage = 'There is no ride to display.';

			// If no bike is displaying, filter bike is checked and connected user doesn't have any bike set, display a message advising to register bikes
			if (isset($errormessage) AND $errormessage == 'There is no ride to display.' AND isset($_POST['filter_bike']) AND is_array($connected_user->getBikes())){
				$submessage = 'You should first register your bike in <a href="/riders/profile.php?id=' .$connected_user->id. '#addBike1">your profile settings</a>.';
			} ?>
			
			<?php // Space for error messages and submessage
			if (isset($errormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; 
			if (isset($submessage)) echo '<div class="fullwidth text-center"><p>' .$submessage. '</p></div>'; ?>
		</div>
		
	</div>
	
</body>
</html>