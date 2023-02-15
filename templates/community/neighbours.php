<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main">
		
		<h2 class="top-title">Neighbours</h2>
		
		<div class="container p-0">

			<div class="cf-map" id="neighboursMap"></div>
			
		</div> <?php 
		
			// Select riders from database according to filter queries
			include '../actions/riders/displayNeighboursAction.php'; ?>
			
		<div class="nbr-container container bg-white"> <?php

			if ($connected_user->lngLat != null) {

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

				} else {
					
					$errormessage = '表示できるデータがありません。';


				} ?>

				<script type="module" src="/scripts/riders/neighbours.js"></script> <?php

			} else {
				
				$errormessage = '「お隣機能」を利用するには、自分の居住地を設定する必要があります。<a href="/profile/edit">こちら</a>にアクセスし、「場所」を「地図で選択」ボタンをクリックして設定してください。';
			
			}
			
			if (isset($errormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; ?>
			
		</div>
	
	</div>
	
</body>
</html>

<script src="/scripts/riders/friends.js"></script>