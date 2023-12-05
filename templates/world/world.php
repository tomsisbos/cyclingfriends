<?php

include '../actions/users/initPublicSession.php';
include '../includes/head.php';

// head-map equivalent ?>
<script src='https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.js'></script>
<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script src="/assets/js/html2canvas.min.js"></script>
<script defer src="/scripts/vendor/scriptjs.js"></script>
<script defer src="/scripts/vendor/requirejs.js"></script>
<link href='https://api.tiles.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.css' rel='stylesheet' />

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
		
		<div class="container-fluid mp-container" id="root"></div>
	
	</div>
	
</body>

<script src="/assets/js/lightbox-script.js"></script>
<script type="module" src="/react/world.js"></script>
<script type="module" src="/react/runtime.js"></script>

</html>