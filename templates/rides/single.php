<?php

include '../actions/users/initSessionAction.php';
include '../actions/rides/rideAction.php';
include '../actions/rides/edit/adminPanelAction.php';
include '../actions/rides/edit/galleryAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map.css" />

<body> <?php

	// If set as private and connected user does not have admin rights on this ride, redirect to the dashboard
	if ($ride->privacy == 'private' AND $ride->author_id != $connected_user->id) {
		header('Location: /');
	}
	
	// If set as Friends only and connected user is not on the friends list on the ride author, redirect to the dashboard
	if ($ride->author_id != $connected_user->id AND $ride->privacy == 'friends_only' AND $ride->getAuthor()->isFriend($connected_user) == false) {
		header('Location: /');
	}

	include '../includes/navbar.php'; ?>

	<div class="main container-shrink"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>

		<div class="container header" style="background-image: <?= 'url(' .$ride->getFeaturedImage()->url. '); background-size: cover;' ?>">
			<div class="header-block">
				<div class="header-row mb-2">
					<div class="rd-status"> <?php
						// Set text color depending on the status
						$status_color = $ride->getStatusColor('background'); ?>
						<p style="background-color: <?= $status_color ?>" class="tag-light text-light"><?= $ride->status;
						// Only add substatus if there is one
						if (!empty($ride->substatus)) echo ' (' .$ride->substatus. ')'; ?></p> <?php 
						if ($ride->privacy == 'friends_only') { ?>
							<p style="background-color: #ff5555" class="tag-light text-light"><?= $ride->getAuthor()->login; ?>の友達に限定</p> <?php
						} ?>
					</div>
				</div>
				<div class="header-row">
					<h2><?= $ride->name ?></h2>
				</div>
				<div class="header-row">
					<a href="/rider/<?= $ride->author_id ?>"><?php $ride->getAuthor()->getPropicElement(30, 30, 30); ?></a>
					<p>by <strong><?= $ride->getAuthor()->login ?></strong></p>
				</div>
				<div class="header-row mt-2"> <?php
					// Include admin buttons if the user has admin rights on this ride
					if ($ride->author_id == $connected_user->id) include '../includes/rides/admin-buttons.php'; 
					// Else, include participation buttons
					else include '../includes/rides/participation-buttons.php'; ?>
				</div>
			</div>
		</div>
			
		<!-- Displays ride participants --> <?php
		include '../includes/rides/participants.php';
			
			// Include admin panel if the user has admin rights on this ride
			if ($ride->author_id == $connected_user->id) include '../includes/rides/admin-panel.php'; ?>
			
			<!-- Infos section -->
			<div class="container margin-bottom">
				<div class="row">
					<div class="col-sm">
						<p><strong>開催日 :</strong> <?= $ride->date; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm">
						<p><strong>集合時間 :</strong> <?= $ride->meeting_time; ?></p>
					</div>
					<div class="col-sm">
						<p><strong>集合場所 :</strong> <?= $ride->meeting_place; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm">
						<p><strong>出発時間 :</strong> <?= $ride->departure_time. " (" .$ride->finish_time. "頃に解散予定)"; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm">
						<p><strong>レベル :</strong> <?= $ride->getAcceptedLevelString(); ?></p>
					</div>
					<div class="col-sm">
						<p><strong>参加可能車種 :</strong> <?= $ride->getAcceptedBikesString(); ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm">
						<p><?= $ride->description; ?></p>
					</div>
				</div>
			</div> <?php

			// Include checkpoints gallery
			include '../includes/rides/checkpoints-gallery.php'; ?>
				
			<!-- Course section -->
			<div class="container margin-bottom d-flow-root"> <?php
				
				if ($ride->getRoute() != null) { ?>
					<div class="rd-course-thumbnail">
						<a href="/ride/<?= $ride->id ?>/route"><img src="<?= $ride->getMapThumbnail() ?>"></img></a>
					</div> <?php
				} ?>
			
				<div class="rd-course-infos">
					<h3>コースについて</h3>
					<p><strong>距離 :</strong> <?php 
						if (isset($ride->finish_place)) echo $ride->distance. "km - " .$ride->meeting_place. " から " .$ride->finish_place. " まで";
						else echo $ride->distance. "km - " .$ride->meeting_place. " から " .$ride->meeting_place. " まで"; ?></p>
					<p><strong>起伏 :</strong> <?= $ride->getTerrainIcon() ?></p>
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

<script src="/assets/js/lightbox-script.js"></script>
<script src="/scripts/rides/checkpoints-gallery.js"></script>