<?php

include '../actions/users/initPublicSessionAction.php';
include '../includes/head.php';
include '../actions/activities/activityAction.php';
$object = $activity;
include '../actions/postCommentAction.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/twitter.css">
<link rel="stylesheet" href="/assets/css/lightbox-style.css">

<body class="relative-navbar">

<?php

	// If set as private and connected user does not have admin rights on this activity, redirect to the dashboard
	if ($activity->privacy == 'Private' AND (!isSessionActive() || $activity->user_id != getConnectedUser()->id)) {
		header('Location: /');
	}
	
	// If set as Friends only and connected user is not on the friends list on the activity author, redirect to the dashboard
	else if ($activity->privacy == 'Friends only' AND (!isSessionActive() || ($activity->user_id != getConnectedUser()->id AND !$activity->getAuthor()->isFriend(getConnectedUser())))) {
		header('Location: /');
	}

	include '../includes/navbar.php'; ?>
	
	<div class="main container-shrink"> <?php
	
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container-fluid"> <?php 

			if ($activity->getFeaturedImage()) { ?> <div class="container header" style="background-image: <?= 'url(' .$activity->getFeaturedImage()->url. '); background-size: cover;'?>"> <?php }
			else { ?> <div class="container header" style="background-image: <?= 'url(/media/default-photo-' . rand(1,9) .'.svg)'?>; background-size: cover;"> <?php } ?>
				<div class="header-block">
					<div class="header-row">
						<h2><?= $activity->title ?></h2>
					</div>
					<div class="header-row">
						<p><?= $activity->datetime->format('Y/m/d') ?></p>
					</div>
					<div class="header-row">
						<div class="header-column">
							<a href="/rider/<?= $activity->user_id ?>"><?php $activity->getAuthor()->getPropicElement(30, 30, 30); ?></a>
						</div>
						<div class="header-column">
							<p>by <a href="/rider/<?= $activity->user_id ?>"><strong><?= $activity->getAuthor()->login ?></strong></a></p>
						</div>
						<div class="header-column"> <?php
							if ($activity->privacy == 'private') { ?>
								<p style="background-color: #ff5555" class="tag-light text-light">非公開</p> <?php
							} else if ($activity->privacy == 'friends_only') { ?>
								<p style="background-color: #ff5555" class="tag-light text-light">友達のみ</p> <?php
							} ?>
						</div>
					</div>
					<div class="header-row mt-2"> <?php
						// Include admin buttons if the user has admin rights on this activity
						if (isSessionActive() && $activity->user_id == getConnectedUser()->id) include '../includes/activities/admin-buttons.php';
						// Include user buttons
						include '../includes/activities/user-buttons.php';?>
					</div>
				</div>
			</div>

			<div class="container p-0 pg-ac-specs-container">
				<div class="pg-ac-spec-container front border-0 <?= $activity->setBackgroundColor('distance')?>">
					<div class="pg-ac-spec-label">距離</div>
					<div class="pg-ac-spec-value"><?= round($activity->route->distance, 1) ?><span class="pg-ac-spec-unit"> km</span></div>
				</div>
				<div class="pg-ac-spec-container back border-0 <?= $activity->setBackgroundColor('elevation')?>" style="display: none">
					<div class="pg-ac-spec-label">獲得標高</div>
					<div class="pg-ac-spec-value"><?= $activity->route->elevation ?><span class="pg-ac-spec-unit"> m</span></div>
				</div>
				<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('duration')?>">
					<div class="pg-ac-spec-label">活動時間</div>
					<div class="pg-ac-spec-value"> <?php
						if (intval($activity->duration_running->h) > 0) {
							if (substr($activity->duration_running->h, 0, 1) == '0') echo substr($activity->duration_running->h, 1, strlen($activity->duration_running->h));
							else echo $activity->duration_running->h ?>
							<span class="my-pg-ac-spec-unit"> h </span>
							<?= $activity->duration_running->i;
						} else {
							if (substr($activity->duration_running->i, 0, 1) == '0') echo substr($activity->duration_running->i, 1, strlen($activity->duration_running->i));
							else echo $activity->duration_running->i; ?>
							<span class="my-pg-ac-spec-unit"> min </span> <?php
						} ?>
					</div>
				</div>
				<div class="pg-ac-spec-container back <?= $activity->setBackgroundColor('break_time')?>" style="display: none;">
					<div class="pg-ac-spec-label">休憩時間</div>
					<div class="pg-ac-spec-value"> <?php
						if (intval($activity->getBreakTime()->h) > 0) {
							if (substr($activity->getBreakTime()->h, 0, 1) == '0') echo substr($activity->getBreakTime()->h, 1, strlen($activity->getBreakTime()->h));
							else echo $activity->getBreakTime()->h ?>
							<span class="my-pg-ac-spec-unit"> h </span>
							<?= $activity->getBreakTime()->i;
						} else {
							if (substr($activity->getBreakTime()->i, 0, 1) == '0') echo substr($activity->getBreakTime()->i, 1, strlen($activity->getBreakTime()->i));
							else echo $activity->getBreakTime()->i ?>
							<span class="my-pg-ac-spec-unit"> min </span> <?php
						} ?>
					</div>
				</div>
				<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('altitude_max')?>">
					<div class="pg-ac-spec-label">最高地点</div>
					<div class="pg-ac-spec-value"><?= $activity->altitude_max ?><span class="pg-ac-spec-unit"> m</span></div>
				</div> 
				<div class="pg-ac-spec-container back <?= $activity->setBackgroundColor('altitude_min')?>" style="display: none;">
					<div class="pg-ac-spec-label">最低地点</div>
					<div class="pg-ac-spec-value"><?= $activity->altitude_min ?><span class="pg-ac-spec-unit"> m</span></div>
				</div>
				<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('speed_avg')?>">
					<div class="pg-ac-spec-label">平均速度</div>
					<div class="pg-ac-spec-value"><?= round($activity->getAverageSpeed(), 1) ?><span class="pg-ac-spec-unit"> km/h</span></div>
				</div>
				<div class="pg-ac-spec-container back <?= $activity->setBackgroundColor('speed_max')?>" style="display: none;">
					<div class="pg-ac-spec-label">最高速度</div>
					<div class="pg-ac-spec-value"><?= round($activity->speed_max, 1) ?><span class="pg-ac-spec-unit"> km/h</span></div>
				</div> <?php
					if ($activity->temperature_avg) { ?>
						<div class="pg-ac-spec-container front <?= $activity->setBackgroundColor('temperature_avg')?>">
							<div class="pg-ac-spec-label">平均気温</div>
							<div class="pg-ac-spec-value"><?= round($activity->temperature_avg, 1) ?><span class="pg-ac-spec-unit"> °C</span></div>
						</div>
						<div class="pg-ac-spec-container back border-0 <?= $activity->setBackgroundColor('temperature_max')?>" style="display: none;">
							<div class="pg-ac-spec-label">最低 - 最高気温</div>
							<div class="pg-ac-spec-value"><?= round($activity->temperature_min, 1) ?> - <?= round($activity->temperature_max, 1) ?><span class="pg-ac-spec-unit"> °C</span></div>
						</div> <?php
					}?>
			</div>

			<div class="container p-0"> <?php
				include '../includes/activities/map.php'; ?>						
			</div>

			<div class="container pg-ac-summary-container"> <?php
				include '../includes/activities/timeline.php'; ?>
			</div>
			
			<div class="container"><?php
				include '../includes/comments-box.php'; ?>
			</div>
		</div>
	</div>

</body>
</html>

<script src="/scripts/vendor.js"></script>
<script src="/node_modules/exif-js/exif.js"></script>
<script type="module" src="/scripts/activities/activity.js"></script>