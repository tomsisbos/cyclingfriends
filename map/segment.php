<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
include '../actions/segments/segmentAction.php'; ?>

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/segment.css">

<body> <?php

	include '../includes/navbar.php';

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
						<button class="btn button" type="button">Export as *.gpx</button>
					</a>
				</div>
			</div>
		</div>
		
		<div class="container pg-sg-section-infos">
			<div class="pg-sg-section-infos-topline">
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