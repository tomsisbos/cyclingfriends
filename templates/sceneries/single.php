<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
include '../actions/sceneries/sceneryAction.php'; ?>

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/segment.css">
<link rel="stylesheet" href="/assets/css/mkpoint.css">
<link rel="stylesheet" href="/assets/css/activity.css">

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		displayMessage(); ?>
		
		<div class="container-fluid"> <?php

			$mkpoint_photos = $mkpoint->getImages(8);
			$main_color = getMainColor($mkpoint->thumbnail); ?>
			<div class="container pg-sg-header" style="background-image: url('data:<?= $mkpoint_photos[0]->type ?>;base64,<?= $mkpoint_photos[0]->blob ?>');">
				<div class="header">
					<div class="text-shadow d-flex flex-column" style="max-width: 50%">
						<h1><?= $mkpoint->name ?></h1>
					</div>
					<div class="tag-light tag-blue"></div>
					<div class="header-buttons">
						<button class="btn button box-shadow js-favorite-button" type="button"> <?php
							if ($mkpoint->isFavorite()) echo 'Remove from favorites';
							else echo 'Add to favorites' ?>
						</button>
					</div>
				</div>
			</div>
			
			<div class="container pg-sg-topline" style="background-color: <?= luminanceLight($main_color, 0.85) ?>"> <?php
				$mkpoint->user->displayPropic() ?>
				<div>
					<div class="pg-sg-location">
						<?= $mkpoint->city . ' (' . $mkpoint->prefecture . ') - ' . $mkpoint->elevation . 'm' ?>
					</div> <?php
					$cleared_activity_id = $mkpoint->isCleared();
					if ($cleared_activity_id) { ?>
						<div id="visited-icon" style="display: inline;" title="この絶景スポットを訪れました。">
							<a href="/activity/<?= $cleared_activity_id ?>" target="_blank">
								<span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
							</a>
						</div> <?php
					} ?>
					<div class="pg-sg-tags"> <?php 
						foreach ($mkpoint->getTags() as $tag_name) {
							$tag = new Tag($tag_name) ?>
							<a href="/tag/<?= $tag->name ?>">
								<div class="popup-tag tag-dark" style="color: #fff; background-color: <?= $main_color?>"> <?= '#' . $tag->getString() ?> </div>
							</a> <?php
						} ?>
					</div>
					<div>by <a href="/rider/<?= $mkpoint->user->id ?>"><?= $mkpoint->user->login ?></a></div>
					<div><div class="popup-rating" style="color: darkgrey"></div></div>
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
			<div class="container">
				<h2>Latest public activities</h2>
				<div class="mk-activities-container"> <?php
					$activities = $mkpoint->findLastRelatedActivities(3);
					if (!empty($activities)) {
						foreach ($activities as $activity) {
							if ($activity->privacy == 'public') include '../includes/activities/small-card.php';
						}
					} else echo '表示できるデータはありません。' ?>
				</div>
			</div>
			<div class="container p-0 pg-sg-map-box">
				<iframe style="width: 100%; height: 100%" src="http://maps.google.com/maps?q=<?= $mkpoint->lngLat->lat ?>,<?= $mkpoint->lngLat->lng ?>&t=k&z=12&output=embed"></iframe>
				<div class="pg-sg-itinerary">
					<div class="pg-sg-itinerary-title">レビュー</div>
					<div class="chat-reviews pt-2"></div>
					<div class="chat-msgbox">
						<textarea id="mkpointReview" class="fullwidth"></textarea>
						<button id="mkpointReviewSend" class="mp-button bg-button text-white">レビューを投稿</button>
					</div>
				</div>
			</div>

		</div>
	</div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>
<script type="module" src="/scripts/sceneries/scenery.js"></script>