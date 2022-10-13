<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
?>

<body>

	<?php // Navbar
	include '../includes/navbar.php';	

	// Space for error messages
	if(isset($errormessage)){ echo '<div class="error-block" style="margin: 0px;"><p class="error-message">' .$errormessage. '</p></div>'; }
	if(isset($successmessage)){ echo '<div class="success-block" style="margin: 0px;"><p class="success-message">' .$successmessage. '</p></div>'; } ?>
	
	<div class="container d-flex flex-column gap end">	
	
		<?php // Settings sidebar
		include '../includes/users/settings/sidebar.php'; ?>

	</div>
	
</body>