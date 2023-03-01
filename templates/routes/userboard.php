<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main">
		
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
							<a href="/route/<?= $route->id ?>">
								<img class="rt-thumbnail" src="<?= $route->getThumbnail() ?>">
							</a>
						</div>
						<div class="rt-infos-container">
							<div class="rt-name">
							<a href="/route/<?= $route->id ?>">
									<?= $route->name ?>
								</a>
								<div class="rt-posting-date">
									<?= $route->posting_date->format('Y/m/d \a\t H\hi'); ?>
								</div>
								<div class="rt-specs d-flex flex-column">
									<div><?= '<strong>距離 : </strong>' .round($route->distance, 1). 'km' ?></div>
									<div><?= '<strong>獲得標高 : </strong>' .$route->elevation. 'm' ?></div>
									<div><?= '<strong>難易度 : </strong>' .$route->getStars($route->calculateDifficulty()) ?></div>
								</div>
							</div>
						</div>
						<div class="append-buttons">
							<a href="/route/<?= $route->id ?>/edit">
								<div class="mp-button success" type="button" name="edit">編集</div>
							</a>
							<a id="export" data-id="<?= $route->id ?>" download>
								<div class="mp-button success" type="button">*.gpx保存</div>
							</a>
							<div class="mp-button danger" data-id="<?= $route->id ?>" id="deleteRoute" type="button" name="delete">削除</div>
						</div>
					</div> <?php
					
				} ?>
			
			</div> <?php
			
			// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
			if (empty($connected_user->getRoutes())) { ?>
				<div class="errormessage">表示できるデータがありません。</div> <?php		
			}
			
			// Set pagination system
			if (isset($_GET['p'])) $p = $_GET['p'];
			else $p = 1;
			$url = strtok($_SERVER["REQUEST_URI"], '?');
			$total_pages = $connected_user->getRoutesNumber() / $limit;
			
			// Build pagination menu
			include '../includes/pagination.php' ?>
		</div>
	</div>

	
</body>
</html>

<script type="module" src="/scripts/routes/export.js"></script>
<script src="/scripts/routes/delete.js"></script>