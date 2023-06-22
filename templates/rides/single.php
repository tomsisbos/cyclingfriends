<?php

include '../actions/users/initPublicSessionAction.php';
include '../actions/rides/rideAction.php';
include '../actions/rides/edit/adminPanelAction.php';
include '../actions/rides/edit/galleryAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map.css" />

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body> <?php

	// If set as private and connected user does not have admin rights on this ride, redirect to the dashboard
	if ($ride->privacy == 'private' AND (!isset($_SESSION['auth']) OR $ride->author_id != $connected_user->id)) {
		header('Location: /');
	}
	
	// If set as Friends only and connected user is not on the friends list on the ride author, redirect to the dashboard
	if ($ride->privacy == 'friends_only' AND (!isset($_SESSION['auth']) OR (isset($_SESSION['auth']) && $ride->author_id != $connected_user->id AND !$ride->getAuthor()->isFriend($connected_user)))) {
		header('Location: /');
	}

	include '../includes/navbar.php'; ?>

	<div class="main container-shrink"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>

		<div class="with-background-img full-header fullwidth js-fade-on-scroll" data-parameter="ride-imgs" data-value="<?= $ride->id ?>" data-overlay-color="#000"></div>
		<div class="full-header with-background-flash" style="position: relative">
			<div class="header-block">
				<div class="header-row mb-2">
					<div class="rd-status">
						<p class="tag-light text-light <?= $ride->getStatusClass(); ?>"><?= $ride->status;
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
					if (isset($_SESSION['auth']) && ($connected_user->id == $ride->author_id OR in_array($connected_user->id, array_map(function ($guide) { return $guide->id; }, $ride->getGuides())))) include '../includes/rides/admin-buttons.php'; 
					// Else, include participation buttons
					else include '../includes/rides/participation-buttons.php'; ?>
				</div>
			</div>
		</div>
			
		<!-- Displays ride participants --> <?php
		include '../includes/rides/participants.php';
			
			// Include admin panel if the user has admin rights on this ride
			if (isset($_SESSION['auth']) && $ride->author_id == $connected_user->id) include '../includes/rides/admin-panel.php'; 
			
			// General infos ?>
			<div class="container ride-infos mb-3">
				<h3>基本情報</h3>
				<div class="row">
					<div class="col-sm">
						<p><strong>開催日 :</strong> <?= $ride->date; ?></p>
					</div>
					<div class="col-sm">
						<p><strong>エントリー :</strong> <?= $ride->entry_start. ' ~ ' .$ride->entry_end; ?></p>
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
						<p><?= $ride->getFormattedDescription(); ?></p>
					</div>
				</div>
			</div>
			
			<div class="container mb-3">
				<h3>集合情報</h3>
				<div class="row">
					<div class="col-sm">
						<p><strong>集合時間 :</strong> <?= $ride->meeting_time; ?></p>
					</div>
					<div class="col-sm">
						<p><strong>出発時間 :</strong> <?= $ride->departure_time. " (" .$ride->finish_time. "頃に解散予定)"; ?></p>
					</div>
				</div>
				<div class="row">
					<div class="col-sm"> <?php
						$first_checkpoint = $ride->getCheckpoints()[0]; ?>
						<p><strong>集合場所 :</strong> <?php
							if ($first_checkpoint->name != 'Start') echo '<a href="https://www.google.com/maps/search/?api=1&query=' .$first_checkpoint->lngLat->lat. '%2C' .$first_checkpoint->lngLat->lng. '&query_place_id=' .$first_checkpoint->name. '" target="_blank">' .$first_checkpoint->name. '</a>・' .$ride->meeting_place; ?>
							<iframe class="mt-3" style="width: 100%; height: 240px" src="https://maps.google.com/maps?q=<?= $first_checkpoint->lngLat->lat ?>,<?= $first_checkpoint->lngLat->lng ?>&t=k&z=17&output=embed"></iframe>
						</p>
					</div>
				</div>
			</div>
			
			<div class="container d-flex flex-column gap mb-3">
				<h3>ガイド</h3><?php
				foreach ($ride->getGuides() as $guide) { ?>
					<div class="guide-card bg-white">
						<a href="<?= $router->generate('profile-single', ['user_id' => $guide->id]) ?>"><?= $guide->getPropicElement() ?></a>
						<div class="guide-text">
							<div class="guide-identity">
								<div class="guide-position"><strong><?= $guide->getPositionString() ?></strong></div>
								<a href="<?= $router->generate('profile-single', ['user_id' => $guide->id]) ?>"><div class="guide-login">@<?= $guide->login ?></div></a>
								<div class="guide-name">(<?= $guide->last_name. ' ' .$guide->first_name ?>)</div>
							</div>
							<div class="guide-description"><?= $guide->description ?></div>
						</div>
					</div> <?php
				} ?>
			</div> <?php

			// Include checkpoints gallery
			include '../includes/rides/checkpoints-gallery.php'; 
			
			// Course section ?>
			<div class="container d-flow-root">
			
				<div class="rd-course-infos">
					<h3>コース</h3>

					<p><strong>距離 :</strong> <?php 
						if (isset($ride->finish_place)) echo $ride->distance. "km - " .$ride->meeting_place. "から" .$ride->finish_place. "まで";
						else echo $ride->distance. "km - " .$ride->meeting_place. "発着"; ?></p>
					<p><strong>起伏 :</strong> <?= $ride->getTerrainIcon() ?></p>
					<p><?= $ride->course_description; ?></p>
				</div>
			</div> <?php

			if ($ride->getRoute() != null) { ?>
			
				<div class="container pt-0 d-flex gap">
					<a href="<?= $router->generate('ride-route', ['ride_id' => $ride->id]); ?>">
						<button class="mp-button bg-button text-white">詳細</button>
					</a> <?php

					include '../includes/routes/export-button.php'; ?>
				
				</div>

				<div class="container p-0"> <?php

					$route = $ride->getRoute(); 
			
					include '../includes/routes/map.php';

					include '../includes/routes/profile.php';

					include '../includes/routes/itinerary.php'; ?>

				</div> <?php

			}
			
			// Chat ?>
			<div class="container my-3"> <?php
				
				include '../includes/rides/chat.php' ?>

			</div>
			
		</div>
	</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>
<script src="/assets/js/fade-on-scroll.js"></script>
<script src="/assets/js/lightbox-script.js"></script>
<script src="/scripts/rides/checkpoints-gallery.js"></script>
<script src="/scripts/riders/friends.js"></script>
<script type="module" src="/scripts/routes/route.js"></script>