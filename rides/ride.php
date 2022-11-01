<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map.css" />

<?php
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
include '../actions/rides/rideAction.php';
include '../actions/rides/edit/adminPanelAction.php';
include '../actions/rides/join&quitAction.php';
include '../actions/rides/edit/galleryAction.php';
?>

<body>

	<?php
	// If set as private and connected user does not have admin rights on this ride, redirect to the dashboard
	if ($ride->privacy == 'Private' AND $ride->author != $connected_user) {
		header('Location: ../dashboard.php');
	}
	
	// If set as Friends only and connected user is not on the friends list on the ride author, redirect to the dashboard
	if ($ride->author != $connected_user AND $ride->privacy == 'Friends only' AND $ride->author->isFriend($connected_user) == false) {
		header('Location: ../dashboard.php');
	}

	include '../includes/navbar.php';

	// Space for general error messages
	if (isset($errormessage)) { echo '<div class="error-block m-0"><p class="error-message">' .$errormessage. '</p></div>'; }
	if (isset($successmessage)) { echo '<div class="success-block m-0"><p class="success-message">' .$successmessage. '</p></div>'; }	
		
	// Set and update all ride proprieties
	include '../actions/rides/convertIntToStringValuesAction.php'; ?>
	
	<div class="container-fluid">
		<div class="container ride-header" style="background-image: <?= 'url(data:image/jpeg;base64,' .$ride->getFeaturedImage()['img']. '); background-size: cover;">'; ?>
			<div class="tr-row gap">
				<div class="td-row">
					<a href="/riders/profile.php?id=<?= $ride->author->id ?>"><?php $ride->author->displayPropic(60, 60, 60); ?></a>
				</div>
				<div class="td-row text-shadow" style="max-width: 50%">
					<h1><?= $ride->name ?></h1>
				</div>
				<div class="td-row">
					<p>by <strong><?= $ride->author->login ?></strong></p>
				</div>
				<div class="td-row flex-column align-self-center">
					<?php // Set text color depending on the status
					$status_color = colorStatus($ride->status)[1]; ?>
					<p style="background-color: <?= $status_color ?>" class="tag-light text-light"><?= $ride->status;
					// Only add substatus if there is one
					if (!empty($ride->substatus)) echo ' (' .$ride->substatus. ')'; ?></p>
					<?php if ($ride->privacy == 'Friends only') { ?>
						<p style="background-color: #ff5555" class="tag-light text-light">Reserved to <?= $ride->author->login; ?>'s friends</p>
					<?php } ?>
				</div>
				<div class="td-row push"> <?php
					// Include admin buttons if the user has admin rights on this ride
					if ($ride->author == $connected_user) {
						include '../includes/rides/admin-buttons.php'; 
					
					// Else, include participation buttons
					} else {
						include '../includes/rides/participation-buttons.php'; 
					} ?>
				</div>
			</div>
		</div>
			
		<!-- Displays ride participants -->
		<?php include '../includes/rides/participants.php';
			
			// Include admin panel if the user has admin rights on this ride
			if ($ride->author == $connected_user) {
				include '../includes/rides/admin-panel.php';
			} ?>
			
			<!-- Infos section -->
			<div class="container margin-bottom">
				<div class="row">
					<div class="col">
						<p><strong>Date :</strong> <?= $ride->date; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<p><strong>Meeting time :</strong> <?= $ride->meeting_time; ?></p>
					</div>
					<div class="col">
						<p><strong>Meeting place :</strong> <?= $ride->meeting_place; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<p><strong>Departure time :</strong> <?= $ride->departure_time. " (finish around " .$ride->finish_time. ")"; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<p><strong>Level :</strong> <?= $ride->getAcceptedLevelString(); ?></p>
					</div>
					<div class="col">
						<p><strong>Accepted bikes :</strong> <?= $ride->getAcceptedBikesString(); ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<p><?= $ride->description; ?></p>
					</div>
				</div>
			</div> <?php

			// Include checkpoints gallery
			include '../includes/rides/checkpoints-gallery.php'; ?>
				
			<!-- Course section -->
			<div class="container margin-bottom d-flow-root"> <?php
				
				if (isset($ride->route)) { ?>
					<div class="rd-course-thumbnail">
						<a href="http://cyclingfriends.co/route.php?id=<?= $ride->route->id ?>&ride=<?= $ride->id ?>"><img src="<?= $ride->getMapThumbnail() ?>"></img></a>
					</div> <?php
				} ?>
			
				<div class="rd-course-infos">
					<h2>About the course</h2>
					<p><strong>Distance :</strong> <?php 
						if (isset($ride->finish_place)) echo $ride->distance. "km from " .$ride->meeting_place. " to " .$ride->finish_place;
						else echo $ride->distance. "km from " .$ride->meeting_place. " to " .$ride->meeting_place; ?></p>
					<p><strong>Terrain :</strong> <?= $terrain_value; ?></p>
					<p><?= $ride->course_description; ?></p>
				</div>
				<div style="clear: both"></div>
			</div>

			<div class="container margin-bottom">
				<!-- Include chat panel -->
				<div style="clear: both; display: block"> <?php
					include '../includes/rides/chat.php' ?>
				</div>
			</div>
			
		</div>
	</div>

</body>
</html>

<?php // Update changes before reloading of the page
include '../actions/rides/edit/adminPanelAction.php';
include '../actions/rides/rideAction.php'; ?>

<script src="/assets/js/lightbox-script.js"></script>
<script src="/includes/rides/checkpoints-gallery.js"></script>