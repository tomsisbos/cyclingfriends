<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<link rel="stylesheet" href="/assets/css/activity.css">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main">
		
		<h2 class="top-title">My Activities</h2>
		
		<div class="container end">
			
			<div class="my-ac-container"> <?php

				// Define offset and number of articles to query
				$limit = 20;
				if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
				else $offset = 0;
			
				///$number = 0;
				forEach ($connected_user->getActivities($offset, $limit) as $activity) {

					$activity = new Activity($activity['id']); ?>

					<div class="my-ac-panel">

						<div class="my-ac-card">

							<div class="my-ac-thumbnail-container">
								<a href="/activity/<?= $activity->id ?>">
									<img class="ac-map-thumbnail" src="<?= $activity->route->thumbnail ?>">
								</a>
							</div>

							<div class="my-ac-infos-container">
								<div class="ac-name">
									<a href="/activity/<?= $activity->id ?>">
										<?= $activity->title ?>
									</a>
								</div>
								<div class="ac-posting-date">
									<?= $activity->datetime->format('Y/m/d') . ' from ' . $activity->datetime->format('H\hi') . ' to ' . $activity->getEndDateTime()->format('H\hi') ; ?>
								</div>
								<div class="ac-place">
									<?= 'From ' . $activity->getPlace()['start']->getString() . ' to ' . $activity->getPlace()['goal']->getString(); ?>
								</div>
								<div class="ac-specs">
									<div class="ac-spec <?= $activity->setBackgroundColor('distance')?> ">
										<div class="ac-spec-label"><strong>Distance : </strong></div>
										<div class="ac-spec-value"><?= round($activity->route->distance, 1) ?><span class="ac-spec-unit"> km</span></div>
									</div>
									<div class="ac-spec <?= $activity->setBackgroundColor('duration')?> ">
										<div class="ac-spec-label"><strong>Duration : </strong></div>
										<div class="ac-spec-value"> <?php
											if (substr($activity->duration->format('H'), 0, 1) == '0') echo substr($activity->duration->format('H'), 1, strlen($activity->duration->format('H')));
											else echo $activity->duration->format('H') ?><span class="ac-spec-unit"> h </span><?= $activity->duration->format('i') ?></div>
									</div>
									<div class="ac-spec <?= $activity->setBackgroundColor('elevation')?> ">
										<div class="ac-spec-label"><strong>Elevation : </strong></div>
										<div class="ac-spec-value"><?= $activity->route->elevation ?><span class="ac-spec-unit"> m</span></div>
									</div>
									<div class="ac-spec <?= $activity->setBackgroundColor('speed')?> ">
										<div class="ac-spec-label"><strong>Avg. Speed : </strong></div>
										<div class="ac-spec-value"><?= $activity->getAverageSpeed() ?><span class="ac-spec-unit"> km/h</span></div>
									</div>
								</div>
							</div>

							<div class="my-ac-photos-container"><?php
								$preview_photos = $activity->getPreviewPhotos(5);
								foreach ($preview_photos as $photo) { ?>
									<div class="my-ac-photo-container"> 
										<img class="my-ac-photo" src="<?= 'data:' . $photo->type . ';base64,' . $photo->blob ?>">
									</div> <?php
								} ?>
							</div>

						</div>
						
						<div class="my-ac-buttons">
							<a href="/activitiy/<?= $activity->id ?>/edit">
								<div class="mp-button btn bg-darkgreen text-white">Edit</div>
							</a>
							<div class="mp-button btn bg-darkred text-white" data-id="<?= $activity->id ?>" id="deleteButton">Delete</div>
						</div>

					</div><?php

				} ?>
			</div> <?php
			
			// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
			if (empty($connected_user->getActivities())) { ?>
				<div class="errormessage">There is no activity to display.</div> <?php		
			}
			
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