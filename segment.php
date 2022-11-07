<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php';
include 'actions/segments/segmentAction.php'; ?>

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/segment.css">

<body> <?php

	include 'includes/navbar.php';

	// Space for general error messages
	if (isset($errormessage)) echo '<div class="error-block m-0"><p class="error-message">' .$errormessage. '</p></div>';
	if (isset($successmessage)) echo '<div class="success-block m-0"><p class="success-message">' .$successmessage. '</p></div>'; ?>
	
	<div class="container-fluid">

		<div class="container pg-sg-header" style="background-image: <?= $segment->getFeaturedImage() ?>; background-size: cover;">
			<div class="tr-row gap">
				<div class="text-shadow d-flex flex-column" style="max-width: 50%">
					<h1><?= $segment->name ?></h1>
				</div> <?php 
				if ($segment->favourite) { ?>
					<div class="td-row">
						<span class="popup-favourite">★</span>
					</div> <?php
				} ?>
				<div class="td-row">
					<div class="tag-light tag-blue"><?= ucfirst($segment->rank) ?></div>
				</div>
				<div class="td-row push">
					<div class="td-row">
						<button class="btn button box-shadow" type="button">Add to favorites</button>
					</div>
					<a id="export" download>
						<button class="btn button box-shadow" type="button">Export as *.gpx</button>
					</a>
				</div>
			</div>
		</div>
		
		<div class="container pg-sg-topline">
			<div class="pg-sg-location">
				<?= $segment->route->startplace ?>
			</div>
			<div class="pg-sg-tags"> <?php 
				foreach ($segment->tags as $tag => $set) {
					if ($tag != 'id' AND $set == 1) { ?>
						<div class="popup-tag tag-dark"> <?= '#' .$tag ?> </div> <?php
					}
				} ?>
			</div>
		</div>

		<div class="container pg-sg-section-infos">
			<div class="pg-sg-infos-main">
				<div class="pg-sg-generalinfos">
					<div class="pg-sg-specs-container">
						<div class="pg-sg-specs">
							<div><strong>Distance : </strong><?= round($segment->route->distance, 1) ?>km</div>
							<div><strong>Elevation : </strong><?= $segment->route->elevation ?>m</div>
						</div>
						<div class="pg-sg-specs">
							<div><strong>Estimated time : </strong><?= $segment->route->calculateEstimatedTime($connected_user->level)->format('H:i') ?></div>
							<div><strong>Difficulty : </strong><?= $segment->route->getStars($segment->route->calculateDifficulty()) ?></div>
						</div>
					</div>
					<div class="pg-sg-description">
						<?= $segment->description ?>
					</div>
				</div> <?php
					if (!empty($segment->advice->name)) { ?>
						<div class="pg-sg-point">
							<div class="popup-advice">
								<div class="popup-advice-name">
									<iconify-icon icon="el:idea" width="20" height="20"></iconify-icon>
									<?= $segment->advice->name ?>
								</div>
								<div class="popup-advice-description">
									<?= $segment->advice->description ?>
								</div>
							</div>
						</div> <?php
					} ?>
			</div>
		</div> <?php

		// Display timeline if seasonal information exist
		if (!empty($segment->seasons)) {

			function getPeriodDetailClass ($number) {
				if ($number == 1) return 'early';
				if ($number == 2) return 'mid';
				if ($number == 3) return 'late';
			} ?>

			<div class="container bg-white">
				<div class="pg-sg-timeline-container">
					<div class="pg-sg-timeline"></div> <?php
					$seasons = $segment->seasons;
					$prepared_seasons = [];
					$season_descriptions = [];
					// For each month of the year
					for ($month = 1; $month <= 12; $month++) { ?>
						<div class="pg-sg-timeline-month">
							<div class="pg-sg-timeline-month-name"><?= $month ?></div>
							<div class="pg-sg-timeline-season"> <?php
								// .. and for each period of these months
								for ($detail = 1; $detail <= 3; $detail++) {
									$advised_season = ['is_now' => false, 'starts_now' => false, 'description' => ''];
									// Iterate seasons data and check if any corresponds to current period
									foreach ($seasons as $season) {
										if (($season->period_start['month'] < $month || ($season->period_start['month'] == $month && $season->period_start['detail'] <= $detail)) && ($season->period_end['month'] > $month || ($season->period_end['month'] == $month && $season->period_end['detail'] >= $detail))) {
											$advised_season['is_now'] = true;
											$advised_season['number'] = $season->number;
											$advised_season['description'] = $season->description;
										}
									}
									// If it does, build season segment and prepare data to display in the description block
									if ($advised_season['is_now'] == true) { ?>
										<div class="pg-sg-seasonline on <?= getPeriodDetailClass($detail) ?>"></div> <?php
										if (!in_array($advised_season['number'], $prepared_seasons)) {
											array_push($season_descriptions, ['month' => $month, 'detail' => $detail, 'description' => $advised_season['description']]);
											array_push($prepared_seasons, $advised_season['number']);
										}
									}/* else ?> <div class="pg-sg-seasonline off"></div> <?php*/
								} ?>
							</div>
						</div> <?php
					} ?>
				</div> <?php 

				// Write season descriptions in front of relevant period segment ?>
				<div class="pg-sg-season-descriptions"> <?php
					for ($month = 1; $month <= 12; $month++) { 
						foreach ($season_descriptions as $season) {
							if ($month == $season['month']) echo '<div class="pg-sg-season-description" style="margin-left: calc((100% / 12 * ' . ($season['month'] - 1) . ') + ' . (($season['detail'] * 33 - 33) / 12) . '%); --margin-left: calc((100% / 12 * ' . ($season['month'] - 1) . ') + ' . (($season['detail'] * 33 - 33) / 12) . '%)"><p>' . $season['description'] . '</p></div>';
						} 
					} ?>
				</div>
			</div> <?php
		}

		// If scenery photos have been found on this route, display them
		$photos = $segment->route->getPhotos();
		if (count($photos) > 0) { ?>
			<div class="container pg-sg-photos-container"> <?php
				foreach ($photos as $photo) { ?>
					<div class="pg-sg-photo">
						<img src="data:<?= $photo->type ?>;base64,<?= $photo->blob ?>"></img>
					</div>
					<?php ///var_dump($photo);
				} ?>
			</div> <?php
		} ?>	

		<div class="container p-0">

			<div class="pg-sg-map-box">
				<div id="segmentMap">
				</div>
				<div class="pg-sg-itinerary">
					<div class="pg-sg-itinerary-title">Itinerary</div> <?php
					foreach ($segment->route->getItinerary() as $spot) { ?>
						<div class="pg-sg-itinerary-spot <?php if (isset($spot['viewed']) AND $spot['viewed'] == true) echo 'text-success' ?>">
							<div class="pg-sg-spot-icon"><span class="iconify" data-icon="bxs:landscape"></span></div>
							<div class="pg-sg-spot-distance">km <?= round($spot['distance'] / 1000, 1) ?></div>
							<div class="pg-sg-spot-name"><?= $spot['name'] ?></div>
						</div> <?php
					} ?>
				</div>
			</div>
			<div id="profileBox" class="container p-0" style="height: 22vh; background-color: white;">
                <canvas id="elevationProfile"></canvas>
            </div>
					
		</div>

	</div>

</body>
</html>

<script src="/map/vendor.js"></script>
<script type="module" src="/segments/segment.js"></script>