<?php

include '../actions/users/initPublicSessionAction.php';
include '../actions/segments/segmentAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/segment.css">

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container-fluid">

			<div class="container header" style="background-image: url(<?= $segment->getFeaturedImage() ?>);">
				<div class="header-block">
					<div class="header-row">
						<h2><?= $segment->name; 
						if ($segment->advised) echo '<span style="color: pink"> ★</span>'; ?></h2>
						<div class="tag-light tag-blue"><?= ucfirst($segment->rank) ?></div>
					</div>
					<div class="header-row"> <?php
						if (isset($_SESSION['auth'])) { ?>
							<button class="mp-button normal js-favorite-button" type="button"> <?php
								if (!$segment->isFavorite()) echo 'お気に入りに追加';
								else echo 'お気に入りから削除'; ?>
							</button> <?php
						} ?>
						<a id="export" download>
							<button class="mp-button normal" type="button">エクスポート</button>
						</a> <?php
						if (isset($_SESSION['auth']) && $connected_user->hasEditorRights()) { ?>
							<a id="delete">
								<button class="mp-button danger">削除</button>
							</a> <?php
						} ?>
					</div>
				</div>
			</div> <?php

			$featured_image = $segment->getFeaturedImage(true);
			if ($featured_image) $main_color = getMainColor($segment->getFeaturedImage(true));
			else $main_color = '#d9d9d9' ?>
			<div class="container pg-sg-topline" style="background-color: <?= luminanceLight($main_color, 0.85) ?>">
				<div class="pg-sg-location">
					<?= $segment->route->startplace ?>
				</div> <?php
				if (isset($_SESSION['auth'])) {
					$cleared_activity_id = $segment->isCleared();
					if ($cleared_activity_id) { ?>
						<div id="visited-icon" style="display: inline;" title="このセグメントを訪れました。">
							<a href="/activity/<?= $cleared_activity_id ?>" target="_blank">
								<span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
							</a>
						</div> <?php
					}
				} ?>
				<div class="pg-sg-tags"> <?php 
					foreach ($segment->tags as $tag_name) {
						$tag = new Tag($tag_name) ?>
						<a href="/tag/<?= $tag->name ?>">
							<div class="popup-tag tag-dark" style="color: #fff; background-color: <?= $main_color?>"> <?= '#' . $tag->getString() ?> </div>
						</a> <?php
					} ?>
				</div>
			</div>

			<div class="container pg-sg-section-infos">
				<div class="pg-sg-infos-main">
					<div class="pg-sg-generalinfos">
						<div class="pg-sg-specs-container">
							<div class="pg-sg-specs">
								<div><strong>距離 : </strong><?= round($segment->route->distance, 1) ?>km</div>
								<div><strong>獲得標高 : </strong><?= $segment->route->elevation ?>m</div>
							</div>
							<div class="pg-sg-specs">
								<div><strong>予測時間 : </strong> <?php
									if (isset($_SESSION['auth'])) echo $segment->route->calculateEstimatedTime($connected_user->level)->format('H:i');
									else echo $segment->route->calculateEstimatedTime(1)->format('H:i') ?>
								</div>
								<div><strong>難易度 : </strong><?= $segment->route->getStars($segment->route->calculateDifficulty()) ?></div>
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
							<img src="<?= $photo->url ?>"></img>
						</div>
						<?php
					} ?>
				</div> <?php
			} ?>	

			<div class="container p-0">

				<div class="pg-sg-map-box">
					<div class="cf-map" id="segmentMap" <?php /*if (isset($_SESSION['auth']) && $connected_user->isPremium())*/ echo 'interactive="true"' ?>> <?php
					/*if (!isset($_SESSION['auth']) || !$connected_user->isPremium()) { ?>
						<a class="staticmap" href="<?= $_SERVER['REQUEST_URI']. '/signin'?>"><img /></a> <?php
					}*/ ?>
					</div>
					<div class="pg-sg-itinerary">
						<div class="p-0 spec-table-container">
							<div class="spec-table-buttons">
								<button id="addToilets" data-entry="toilets" class="mp-button bg-button text-white">トイレを追加</button>
								<button id="addWater" data-entry="water" class="mp-button bg-button text-white">給水場を追加</button>
								<button id="addKonbinis" data-entry="konbinis" class="mp-button bg-button text-white">コンビニを追加</button>
							</div>
							<div class="spec-table">
								<table id="routeTable">
									<tbody>
										<tr class="spec-table-th">
											<th class="table-element e20 text-left">距離</th>
											<th class="table-element e10 text-center">種類</th>
											<th class="table-element e40 text-left">名称</th>
											<th class="table-element e20 text-center">場所</th>
											<th class="table-element e15 text-center">標高</th>
											<th class="table-element e25 text-center">コースまで</th>
										</tr>
										<tr class="loader-center"></tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
					<div class="pg-sg-profile container p-0" id="profileBox" style="height: 22vh; background-color: white;">
						<canvas id="elevationProfile"></canvas>
					</div>
				</div>
						
			</div>

		</div>
	</div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>
<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/scripts/segments/segment.js"></script>