<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
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

			<div style="width: 200px; height: 200px; background-color: yellow" id="board"></div>

		</div>
	
	</div>

    <!-- Note: when deploying, replace "development.js" with "production.min.js". -->
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>

    <!-- Load our React component. -->
    <script type="module" src="/scripts/user/settings.js"></script>
	
</body>