<?php

include '../actions/users/initSession.php';
include '../includes/head.php';

// Redirect to routes if connected user is not author
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
$slug = array_slice($url_fragments, -2)[0];
$route = new Route($slug);
if (getConnectedUser()->id != $route->author->id) header('location: /routes') ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
		
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container-fluid mp-container">

			<div class="cf-map" id="EditRouteMap" class="mp-map"></div>

		</div>
	</div>
	
</body>

<script src="/scripts/vendor.js"></script>
<script type="module" src="/class/utils/CFUtils.js"></script>
<script type="module" src="/scripts/routes/edit.js"></script>

</html>