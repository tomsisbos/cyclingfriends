<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

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

			<div id="EditRouteMap" class="mp-map"></div>

		</div>
	</div>
	
</body>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/map/class/CFUtils.js"></script>
<script type="module" src="/scripts/routes/edit.js"></script>

</html>