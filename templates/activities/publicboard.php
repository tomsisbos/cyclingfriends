<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css"> 

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main">
	
		<h2 class="top-title">アクティビティ一覧</h2>
		
		<div class="container container-transparent end"> 
			
			<div class="ac-container"> <?php
			
				// Define offset and number of articles to query
				define("PREVIEW_PHOTOS_QUANTITY", 5);
				$limit = 10;
				if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
				else $offset = 0; 

				forEach ($connected_user->getPublicActivities($offset, $limit) as $activity) {
					$activity = new Activity($activity['id']);
					if ($activity->hasAccess($connected_user)) {
						include '../includes/activities/card.php';
					}
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