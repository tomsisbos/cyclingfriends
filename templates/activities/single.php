<!DOCTYPE html>
<html lang="jp"> <?php

require_once '../includes/functions.php';
include '../actions/users/initPublicSession.php';
include '../actions/activities/activity.php';

$ogp = [
	'title' => $activity_title,
	'description' => $activity_title,
	'image' => isset($activity_featured_image_url) ? $activity_featured_image_url : $activity_route_thumbnail_url
];

include '../includes/head.php';
include '../includes/head-map.php';
include '../actions/twitter/authentification.php'; ?>

<link rel="stylesheet" href="/assets/css/activity.css" />
<link rel="stylesheet" href="/assets/css/twitter.css">
<link rel="stylesheet" href="/assets/css/lightbox-style.css">

<body>

	<?php // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for error messages
		include '../includes/result-message.php'; ?>
		
		<div id="activity"

			data-activity="<?= $activity_id ?>"
			data-title="<?= $activity_title ?>" <?php
			if (isset($activity_featured_image_url)) echo 'data-featured-image-url="' .$activity_featured_image_url. '"';

			// Twitter data
			if (getConnectedUser()) {
				$twitter = getConnectedUser()->getTwitter();
				if (!$twitter->isUserConnected()) {
					$_SESSION['redirect_uri'] = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
					echo 'data-twitter-auth-url="' .$twitter_auth_url. '"';
				}
				if ($twitter->isUserConnected()) echo 'data-twitter-username="' .$twitter->username. '" data-twitter-name="' .$twitter->name. '" data-twitter-profile-image="' .$twitter->profile_image. '"';
			} ?>

		></div>
	
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Load React component -->
    <script type="module" src="/react/runtime.js"></script>
    <script type="module" src="/react/activity.js"></script>
	
</body>