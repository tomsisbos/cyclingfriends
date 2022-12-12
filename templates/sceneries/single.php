<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
include '../actions/sceneries/sceneryAction.php'; ?>

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/segment.css">
<link rel="stylesheet" href="/assets/css/mkpoint.css">

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		displayMessage(); ?>
		
		<div class="container-fluid"> <?php

			$mkpoint_photos = $mkpoint->getImages(); ?>
			<div class="container pg-sg-header" style="background-image: url('data:<?= $mkpoint_photos[0]->type ?>;base64,<?= $mkpoint_photos[0]->blob ?>');">
				<div class="header">
					<div class="text-shadow d-flex flex-column" style="max-width: 50%">
						<h1><?= $mkpoint->name ?></h1>
					</div>
					<div class="tag-light tag-blue"></div>
					<div class="header-buttons">
						<button id="favoriteButton" class="btn button box-shadow" type="button">Add to favorites</button>
					</div>
				</div>
			</div>
			
			<div class="container pg-sg-topline"> <?php
				$mkpoint->user->displayPropic() ?>
				<div class="d-flex flex-column">
					<div class="pg-sg-location">
						<?= $mkpoint->city . ' (' . $mkpoint->prefecture . ') - ' . $mkpoint->elevation . 'm' ?>
					</div>
					<div>by <a href="/rider/<?= $mkpoint->user->id ?>"><?= $mkpoint->user->login ?></a></div>
					<div><div class="popup-rating"></div></div>
				</div>
			</div>

			<div class="container pg-sg-section-infos">
				<div class="pg-sg-infos-main">
					<div class="pg-sg-generalinfos">
						<div class="pg-sg-description">
							<?= $mkpoint->description ?>
						</div>
					</div>
				</div>
			</div>
			<div class="container pg-sg-photos-container"> <?php
				$number = 1;
				foreach ($mkpoint_photos as $photo) { ?>
					<div class="pg-sg-photo js-clickable-thumbnail" data-number="<?= $number ?>" data-author="<?= $photo->user_id ?>" data-id="<?= $photo->id ?>">
						<img class="mk-thumbnail" src="data:<?= $photo->type ?>;base64,<?= $photo->blob ?>"></img>
					</div> <?php
					$number++;
				} ?>
			</div>
			<div class="container p-0 pg-sg-map-box">
				<iframe style="width: 100%; height: 100%" src="http://maps.google.com/maps?q=<?= $mkpoint->lngLat->lat ?>,<?= $mkpoint->lngLat->lng ?>&z=10&output=embed"></iframe>
				<div class="pg-sg-itinerary">
					<div class="pg-sg-itinerary-title">Reviews</div>
					<div class="chat-reviews pt-2"></div>
					<div class="chat-msgbox">
						<textarea id="mkpointReview" class="fullwidth"></textarea>
						<button id="mkpointReviewSend" class="mp-button bg-button text-white">Post review</button>
					</div>
				</div>
			</div>

		</div>
	</div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>
<script type="module" src="/scripts/sceneries/scenery.js"></script>