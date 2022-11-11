<head>
	<meta charset="UTF-8">
	
	<!-- Title -->
	<title>Cyclingfriends</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
		
	<!-- CSS -->
		<link rel="stylesheet" href="/assets/css/bootstrap/bootstrap.css" />
		<link rel="stylesheet" href="/assets/css/bootstrap/bootstrap-reboot.css" />
		<link rel="stylesheet" href="/assets/css/style.css" />
		<link rel="stylesheet" href="/assets/css/global-map.css" />
		<link rel="stylesheet" href="/assets/css/sidebars.css">
		<link rel="stylesheet" href="/assets/css/riders.css" />
		<link rel="stylesheet" href="/assets/css/routes.css" />
		<link rel="stylesheet" href="/assets/css/mailbox.css" />
		<link rel="stylesheet" href="/assets/css/map.css" />
		<link rel='stylesheet' href='https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.css'/>
	
	<!-- php -->
		<?php
			require 'functions.php';
			require $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php';
			Autoloader::register();
		 ?>
	
	<!-- js -->
		<!-- Jquery -->
			<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<!-- Ajax -->
			<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<!-- Bootstrap -->
			<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<!-- Iconify framework -->
			<script src="https://code.iconify.design/2/2.1.1/iconify.min.js"></script>
		<!-- Mapbox GL JS : mapping library -->
			<script src='https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.js'></script>
		<!-- Turf.js : calculating distances, etc -->
			<script src="https://unpkg.com/@turf/turf@6/turf.min.js"></script>
		<!-- Chart.js : drawing charts like elevation profile -->
			<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
		<!-- html2canvas -->
			<script src="/assets/js/html2canvas.min.js"></script>
		<!-- functions -->
			<script src="/assets/js/functions.js"></script>
		<!-- Animated background -->
			<script src="/assets/js/animated-background.js" defer></script>
		<!-- togpx -->
        	<script src='/node_modules/togpx/togpx.js'></script>
	
	<!-- Polyfills -->
		<!-- container queries -->
		<script src="https://cdn.jsdelivr.net/npm/container-query-polyfill@1/dist/container-query-polyfill.modern.js"></script>
		
</head>