<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main">
		
		<h2 class="top-title">マイアクティビティ</h2>
		
		<div class="container">
			
			<div class="my-ac-container"> <?php

				// Define offset and number of articles to query
				$limit = 7;
				if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
				else $offset = 0;
			
				///$number = 0;
				forEach ($connected_user->getActivities($offset, $limit) as $activity) {

					$activity = new Activity($activity['id']); ?>

					<div class="my-ac-panel">
						
						<div class="append-buttons">
							<div class="my-ac-publication-date">
								<strong>投稿時間：</strong><?= $activity->route->posting_date->format('Y-m-d H:i:s') ?>
							</div>
							<a href="/activity/<?= $activity->id ?>/edit">
								<div class="mp-button success">編集</div>
							</a>
							<div class="mp-button danger" data-id="<?= $activity->id ?>" id="deleteButton">削除</div>
						</div>

						<div class="my-ac-card">

							<div class="my-ac-thumbnail-container">
								<a href="/activity/<?= $activity->id ?>">
									<img class="ac-map-thumbnail" src="<?= $activity->route->getThumbnail() ?>">
								</a> <?php
								if ($activity->privacy != 'public') { ?>
									<p style="background-color: #ff5555" class="tag-on-div tag-light text-light"><?= $activity->getPrivacyString() ?></p> <?php
								} ?>
							</div>

							<div class="my-ac-infos-container">
								<div class="ac-name">
									<a href="/activity/<?= $activity->id ?>">
										<?= $activity->title ?>
									</a>
								</div>
								<div class="ac-posting-date">
									<?= $activity->datetime->format('Y/m/d') . ' - 出発：' . $activity->datetime->format('H\:i') . ' 到着：' . $activity->getEndDateTime()->format('H\:i') ; ?>
								</div>
								<div class="ac-place">
									<?= $activity->getPlace()['start']->toString() . 'から' . $activity->getPlace()['goal']->toString() . 'まで'; ?>
								</div>
								<div class="ac-specs">
									<div class="ac-spec <?= $activity->setBackgroundColor('distance')?> ">
										<div class="ac-spec-label"><strong>距離 : </strong></div>
										<div class="ac-spec-value"><?= round($activity->route->distance, 1) ?><span class="ac-spec-unit"> km</span></div>
									</div>
									<div class="ac-spec <?= $activity->setBackgroundColor('duration')?> ">
										<div class="ac-spec-label"><strong>時間 : </strong></div>
										<div class="ac-spec-value"> <?php
											if (substr($activity->duration->format('H'), 0, 1) == '0') echo substr($activity->duration->format('H'), 1, strlen($activity->duration->format('H')));
											else echo $activity->duration->format('H') ?><span class="ac-spec-unit"> h </span><?= $activity->duration->format('i') ?></div>
									</div>
									<div class="ac-spec <?= $activity->setBackgroundColor('elevation')?> ">
										<div class="ac-spec-label"><strong>獲得標高 : </strong></div>
										<div class="ac-spec-value"><?= $activity->route->elevation ?><span class="ac-spec-unit"> m</span></div>
									</div>
									<div class="ac-spec <?= $activity->setBackgroundColor('speed')?> ">
										<div class="ac-spec-label"><strong>平均速度 : </strong></div>
										<div class="ac-spec-value"><?= $activity->getAverageSpeed() ?><span class="ac-spec-unit"> km/h</span></div>
									</div>
								</div>
							</div>

							<div class="my-ac-photos-container"><?php
								$preview_photos = $activity->getPreviewPhotos(5);
								foreach ($preview_photos as $photo) { ?>
									<div class="my-ac-photo-container<?php if ($photo->featured) echo ' featured' ?>"> 
										<img class="my-ac-photo" src="<?= $photo->url ?>">
									</div> <?php
								} ?>
							</div>

						</div>

					</div><?php

				}
			
				// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
				if (empty($connected_user->getActivities())) { ?>
					<div class="error-block"><div class="error-message">表示するデータがありません。</div></div> <?php		
				} ?>

			</div> <?php
			
			// Set pagination system
			if (isset($_GET['p'])) $p = $_GET['p'];
			else $p = 1;
			$url = strtok($_SERVER["REQUEST_URI"], '?');
			$total_pages = $connected_user->getActivitiesNumber() / $limit;
			
			// Build pagination menu ?>
			<div class="pages"> <?php
				if ($p > 2) { ?>
					<a href="<?= $url. '?p=' .($p - 2) ?>">
						<div class="pages-number">
							<?= $p - 2; ?>
						</div>
					</a> <?php
				}
				if ($p > 1) { ?>
					<a href="<?= $url. '?p=' .($p - 1) ?>">
						<div class="pages-number">
							<?= $p - 1; ?>
						</div>
					</a> <?php
				} ?>
				<a href="<?= $url. '?p=' .$p ?>">
					<div class="pages-number pages-number-selected">
						<?= $p ?>
					</div>
				</a> <?php
				if ($p < $total_pages) { ?>
					<a href="<?= $url. '?p=' .($p + 1) ?>">
						<div class="pages-number">
							<?= $p + 1; ?>
						</div>
					</a> <?php
				}
				if ($p < $total_pages - 1) { ?>
					<a href="<?= $url. '?p=' .($p + 2) ?>">
						<div class="pages-number">
							<?= $p + 2; ?>
						</div>
					</a> <?php
				} ?>
			</div>
		</div>
	</div>

	
</body>
</html>

<script src="/scripts/activities/delete.js"></script>
<script src="/scripts/activities/userboard.js"></script>