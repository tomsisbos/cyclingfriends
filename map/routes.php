<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php'; ?>
	
	<h2 class="top-title">My Routes</h2>
	
	<div class="container end">

		<div class="rt-container"> <?php

			// Define offset and number of articles to query
			$limit = 20;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;
		
			forEach ($connected_user->getRoutes($offset, $limit) as $route) {

				$route = new Route($route['id']); ?>

				<div class="rt-card">
					<div class="rt-thumbnail-container">
						<a href="/map/route.php?id=<?= $route->id ?>">
							<img class="rt-thumbnail" src="<?= $route->thumbnail ?>">
						</a>
					</div>
					<div class="rt-infos-container">
						<div class="rt-name">
						<a href="/map/route.php?id=<?= $route->id ?>">
								<?= $route->name ?>
							</a>
							<div class="rt-posting-date"> <?php
								$posting_date = new DateTime($route->posting_date);
								echo $posting_date->format('Y/m/d \a\t H\hi'); ?>
							</div>
							<div class="rt-specs d-flex flex-column">
								<div><?= '<strong>Distance : </strong>' .round($route->distance, 1). 'km' ?></div>
								<div><?= '<strong>Elevation : </strong>' .$route->elevation. 'm' ?></div>
								<div><?= '<strong>Difficulty : </strong>' .$route->getStars($route->calculateDifficulty()) ?></div>
							</div>
						</div>
					</div>
				</div> <?php
				
			} ?>
		
		</div> <?php
		
		// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
		if (empty($connected_user->getRoutes())) { ?>
			<div class="errormessage">There is no route to display.</div> <?php		
		}
		
		// Set pagination system
		if (isset($_GET['p'])) $p = $_GET['p'];
		else $p = 1;
		$url = strtok($_SERVER["REQUEST_URI"], '?');
		$total_pages = $connected_user->getRoutesNumber() / $limit;
		
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

	
</body>
</html>