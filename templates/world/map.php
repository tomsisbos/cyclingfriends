<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
	
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container-fluid mp-container">

			<div class="mp-map cf-map" id="worldMap"></div>
		
		</div>
	
	</div>
	
</body>

<script src="/scripts/vendor.js"></script>
<script src="/assets/js/lightbox-script.js"></script>
<script type="module" src="/scripts/world/world.js"></script>

</html>