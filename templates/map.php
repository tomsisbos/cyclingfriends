<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
	
		// Space for error messages
		displayMessage(); ?>
		
		<div class="container-fluid mp-container">

			<div id="worldMap" class="mp-map"></div>
		
		</div>
	
	</div>
	
</body>

<script src="/scripts/map/vendor.js"></script>
<script src="/assets/js/lightbox-script.js"></script>
<script type="module" src="/scripts/map/map.js"></script>

</html>