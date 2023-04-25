<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
		
		// Space for error messages
		displayMessage(); ?>
		
		<div class="container-fluid mp-container">

			<div class="cf-map" id="EditRouteMap" class="mp-map"></div>

		</div>
	</div>
	
</body>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/class/utils/CFUtils.js"></script>
<script type="module" src="/scripts/routes/edit.js"></script>

</html>