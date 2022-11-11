<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
?>

<body>

	<?php // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for error messages
		displayMessage(); ?>
		
		<div class="container d-flex flex-column gap end">	
		
			<?php // Settings sidebar
			include '../includes/users/settings/sidebar.php'; ?>

		</div>
	
	</div>
	
</body>