<?php

include '../actions/users/initSession.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="https://api.mapbox.com/mapbox-gl-js/plugins/mapbox-gl-geocoder/v5.0.0/mapbox-gl-geocoder.css" type="text/css">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
		
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container-fluid mp-container">

			<div class="cf-map" id="BuildRouteMap" class="mp-map"></div>

		</div>
	</div>
	
</body>

<script src="/scripts/vendor.js"></script>
<script type="module" src="/class/utils/CFUtils.js"></script>
<script type="module" src="/scripts/routes/new.js"></script>

</html>