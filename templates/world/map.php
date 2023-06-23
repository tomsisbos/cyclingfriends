<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />

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