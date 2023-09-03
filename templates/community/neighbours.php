<?php

include '../actions/users/initSession.php';
include '../includes/head.php';
include '../includes/head-map.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php';
		
	$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
	require $folder . '/actions/blobStorage.php'; ?>

	<div class="main overflow-auto">

		<div id="neighboursMapContainer" style="height: 60vh">
			<div class="cf-map" id="neighboursMap" data-storageurl="<?= $blobClient->getPsrPrimaryUri()->__toString() ?>"></div>
			<div class="grabber"></div>
		</div> <?php 
		
		// Select riders from database according to filter queries
		$limit = 6;
		include '../actions/riders/displayNeighbours.php';
		
		// Get an array of users
		$i = 0;
		for ($i = 0; $i < $limit; $i++) {
			$rider = new User($riders_data[$i]['id']);
			$rider->distance = $riders_data[$i]['distance'];
			array_push($riders, $rider);
		} ?>
			
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
					
					echo '活動拠点がもっとも近いユーザーのみ表示しています。';

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