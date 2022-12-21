<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
include '../actions/activities/activityAction.php'; ?>

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/lightbox-style.css">

<body>

<?php
	// If set as private and connected user does not have admin rights on this activity, redirect to the dashboard
	if ($activity->privacy == 'Private' AND $activity->user != $connected_user) {
		header('Location: /');
	}
	
	// If set as Friends only and connected user is not on the friends list on the activity author, redirect to the dashboard
	if ($activity->privacy == 'Friends only' AND $activity->user != $connected_user AND $activity->user->isFriend($connected_user) == false) {
		header('Location: /');
	}

	include '../includes/navbar.php'; ?>
	
	<div class="main container-shrink"> <?php
	
		// Space for error messages
		displayMessage(); ?>
		
		<div class="container-fluid"> <?php 

			if ($activity->getFeaturedImage()) { ?> <div class="container pg-ac-header" style="background-image: <?= 'url(data:image/jpeg;base64,' .$activity->getFeaturedImage()->blob. '); background-size: cover;'?>;"> <?php
			} else { ?> <div class="container pg-ac-header" style="background-image: <?= 'url(/media/default-photo-' . rand(1,9) .'.svg)'?>; background-size: cover;"> <?php } ?>
				<div class="tr-row gap">
					<div class="td-row">
						<a href="/rider/<?= $activity->user->id ?>"><?php $activity->user->displayPropic(60, 60, 60); ?></a>
					</div>
					<div class="text-shadow d-flex flex-column" style="max-width: 50%">
						<h1><?= $activity->title ?></h1>
						<p><?= $activity->datetime->format('Y/m/d') ?></p>
					</div>
					<div class="td-row text-shadow">
						<p>by <a href="/rider/<?= $activity->user->id ?>"><strong><?= $activity->user->login ?></strong></a></p>
					</div>
					<div class="td-row flex-column align-self-center"> <?php
						if ($activity->privacy == 'private') { ?>
							<p style="background-color: #ff5555" class="tag-light text-light">Private</p> <?php
						} else if ($activity->privacy == 'friends_only') { ?>
							<p style="background-color: #ff5555" class="tag-light text-light">Friends only</p> <?php
						} ?>
					</div>
					<div class="header-buttons"> <?php
						// Include admin buttons if the user has admin rights on this activity
						if ($activity->user == $connected_user) include '../includes/activities/admin-buttons.php'; /*
						// Else, include user buttons
						else include 'includes/activities/user-buttons.php'; */?>
					</div>
				</div>
			</div> <?php
				
			/*
			// Include admin panel if the user has admin rights on this activity
			if ($ride->author == $connected_user) {
				include 'includes/activity/admin-panel.php';
			}*/ ?>

			<div class="container p-0 pg-ac-specs-container">
				<div class="pg-ac-spec-container front border-0 <?= $activity->setBackgroundColor('distance')?>">
					<div class="pg-ac-spec-label">Distance</div>
					<div class="pg-ac-spec-value"><?= $activity->route->distance ?><span class="pg-ac-spec-unit"> km</span></div>
				</div>
				<div class="pg-ac-spec-container back border-0 <?= $activity->setBackgroundColor('altitude_max')?>" style="display: none;">
					<div class="pg-ac-spec-label">Max. Altitude</div>
					<div class="pg-ac-spec-value"><?= $activity->altitude_max ?><span class="pg-ac-spec-unit"> m</span></div>
				</div> 
				<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('duration')?>">
					<div class="pg-ac-spec-label">Duration</div>
					<div class="pg-ac-spec-value"> <?php
						if (substr($activity->duration->format('H'), 0, 1) == '0') echo substr($activity->duration->format('H'), 1, strlen($activity->duration->format('H')));
						else echo $activity->duration->format('H') ?>
						<span class="my-pg-ac-spec-unit"> h </span>
						<?= $activity->duration->format('i') ?>
					</div>
				</div>
				<div class="pg-ac-spec-container back <?= $activity->setBackgroundColor('break_time')?>" style="display: none;">
					<div class="pg-ac-spec-label">Break time</div>
					<div class="pg-ac-spec-value"> <?php
						if (intval($activity->getBreakTime()->format('H')) > 1) {
							if (substr($activity->getBreakTime()->h, 0, 1) == '0') echo substr($activity->getBreakTime()->h, 1, strlen($activity->getBreakTime()->h));
							else echo $activity->getBreakTime()->h ?>
							<span class="my-pg-ac-spec-unit"> h </span>
							<?= $activity->getBreakTime()->format('i');
						} else {
							if (substr($activity->getBreakTime()->i, 0, 1) == '0') echo substr($activity->getBreakTime()->i, 1, strlen($activity->getBreakTime()->i));
							else echo $activity->getBreakTime()->i ?>
							<span class="my-pg-ac-spec-unit"> min </span> <?php
						} ?>
					</div>
				</div>
				<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('elevation')?>">
					<div class="pg-ac-spec-label">Elevation</div>
					<div class="pg-ac-spec-value"><?= $activity->route->elevation ?><span class="pg-ac-spec-unit"> m</span></div>
				</div>
				<div class="pg-ac-spec-container back <?= $activity->setBackgroundColor('slope_max')?>" style="display: none;">
					<div class="pg-ac-spec-label">Max. slope</div>
					<div class="pg-ac-spec-value"><?= $activity->slope_max ?><span class="pg-ac-spec-unit"> %</span></div>
				</div> 
				<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('speed_avg')?>">
					<div class="pg-ac-spec-label">Average speed</div>
					<div class="pg-ac-spec-value"><?= $activity->getAverageSpeed() ?><span class="pg-ac-spec-unit"> km/h</span></div>
				</div>
				<div class="pg-ac-spec-container back <?= $activity->setBackgroundColor('speed_max')?>" style="display: none;">
					<div class="pg-ac-spec-label">Max. speed</div>
					<div class="pg-ac-spec-value"><?= $activity->speed_max ?><span class="pg-ac-spec-unit"> km/h</span></div>
				</div> <?php
					if ($activity->temperature_avg) { ?>
						<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('temperature_avg')?>">
							<div class="pg-ac-spec-label">Avg. temperature</div>
							<div class="pg-ac-spec-value"><?= $activity->temperature_avg ?><span class="pg-ac-spec-unit"> °C</span></div>
						</div>
						<div class="pg-ac-spec-container back border-0 <?= $activity->setBackgroundColor('temperature_max')?>" style="display: none;">
							<div class="pg-ac-spec-label">Min. - Max. temperature</div>
							<div class="pg-ac-spec-value"><?= $activity->temperature_min ?> - <?= $activity->temperature_max ?><span class="pg-ac-spec-unit"> °C</span></div>
						</div> <?php
					}?>
			</div>

			<div class="container p-0">

				<div id="activityMapContainer">
					<div class="cf-map" id="activityMap" <?php if ($connected_user->isPremium()) echo 'interactive="true"' ?>>
						<img class="staticmap"></img>
					</div>
					<div class="grabber"></div>
				</div>
				<div id="profileBox" class="container p-0" style="height: 22vh; background-color: white;">
					<canvas id="elevationProfile"></canvas>
				</div>
						
			</div>

			<div class="container pg-ac-summary-container">
					<div class="pg-ac-timeline">
					</div>
					<div class="pg-ac-checkpoints-container"> <?php
						foreach ($activity->getCheckpoints() as $checkpoint) { ?>
							<div class="pg-ac-checkpoint-container" id="checkpoint<?= $checkpoint->number ?>" data-number="<?= $checkpoint->number ?>">
								<div class="pg-ac-photos-container"> <?php
									foreach ($checkpoint->getPhotos() as $photo) { ?>
										<div class="pg-ac-photo-container">
											<img class="pg-ac-photo" data-id="<?= $photo->id ?>" src="data:image/jpeg;base64,<?= $photo->blob ?>" />
										</div> <?php
									} ?>
								</div>
								<div class="pg-ac-checkpoint-topline">
									<?= $checkpoint->getIcon() . ' km ' . $checkpoint->distance; ?>
									<span class="pg-ac-checkpoint-time"> <?php
										$time = $checkpoint->datetime->diff($activity->getCheckpoints()[0]->datetime);
										if ($time->h != 0 AND $time->i != 0) {
											echo ' (';
											if ($time->h > 0) {
												if (substr($time->h, 0, 1) == '0') echo substr($time->h, 1, strlen($time->h)) . 'h';
												else echo $time->h . 'h';
												if (strlen($time->i) == 1) echo '0' . $time->i;
												else echo $time->i;
											} else {
												echo $time->i . ' min'; 
											}
											echo ') ';
										} ?>
									</span>
									<?= ' - ' . $checkpoint->name ?>
								</div>
								<div class="pg-ac-checkpoint-story">
									<?= $checkpoint->story ?>
								</div>
							</div> <?php
						} ?>
					</div>
				</div>	
		</div>
	</div>

</body>
</html>

<script src="/scripts/map/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/scripts/activities/activity.js"></script>