<?php

include '../actions/users/initPublicSession.php';
include '../includes/head.php';
include '../includes/head-map.php';
include '../actions/activities/activity.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css" />

<body>

	<?php // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for error messages
		include '../includes/result-message.php'; ?>
		
		<div id="activity"
			data-activity="<?= $activity_id ?>"
			data-title="<?= $activity_title ?>" <?php
			if (isset($activity_featured_image_url))  echo 'data-featured-image-url="' .$activity_featured_image_url. '"' ?>
		></div>
	
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Load React component -->
    <script type="module" src="/react/runtime.js"></script>
    <script type="module" src="/react/activity.js"></script>
	
</body>