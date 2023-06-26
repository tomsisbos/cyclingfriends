<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main overflow-auto">
		
		<h2 class="top-title">お隣さん</h2>

		<div id="neighboursMapContainer" style="height: 40vh">
			<div class="cf-map" id="neighboursMap"></div>
			<div class="grabber"></div>
		</div> <?php 
		
		// Select riders from database according to filter queries
		include '../actions/riders/displayNeighboursAction.php';
		
		// Define offset (limit is defined in the action script)
		if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
		else $offset = 0; ?>
			
		<div class="nbr-container container bg-white"> <?php

			// Only allow display if connected user has specified location
			if (getConnectedUser()->lngLat != null) {

				if ($getRiders->rowCount() > 0) {
					foreach ($riders as $rider) { ?>					
						<div class="nbr-card" id="card<?= $rider->id ?>"> <?php
							include '../includes/riders/rider-card.php'; ?>
							<div class="nbr-infos">
								<div class="nbr-distance"><?= $rider->distance ?>km</div> - 
								<div class="nbr-city"><?= $rider->location->toString() ?></div>
							</div>
						</div> <?php
					}
					
					if ($getRiders->rowCount() > $limit) echo '...and others';

				} else {
					
					$errormessage = '表示できるデータがありません。';


				} ?>

				<script type="module" src="/scripts/riders/neighbours.js"></script> <?php

			} else {
				
				$errormessage = '「お隣さん機能」を利用するには、活動拠点を設定する必要があります。<a href="/profile/edit">こちら</a>にアクセスし、「場所」を「地図で選択」ボタンをクリックして設定してください。';
			
			}
			
			if (isset($errormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; ?>
			
		</div>
	
	</div>
	
</body>
</html>

<script src="/scripts/riders/friends.js"></script> <?php
if (getConnectedUser()->lngLat == null) echo '<script src="/scripts/helpers/community/neighbours.js"></script>'; ?>