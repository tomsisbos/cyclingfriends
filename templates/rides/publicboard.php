<?php

include '../actions/users/initSession.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />

<body>

	<?php include '../includes/navbar.php'; ?>
	
	<div class="main">
	
		<h2 class="top-title">ライド掲示板</h2>
		
		<div class="container end">
		
			<?php // Filter options
			include '../includes/rides/filter-options.php';
		
			// Define offset and number of rides to query
			$limit = 6;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;
			
			// Select rides from database according to filter queries
			include '../actions/rides/display.php'; 
			
			// Display ride cards ?>
			
			<div class="rd-cards"> <?php

				if ($getRides->rowCount() > 0) {
					while ($ride = $getRides->fetch()) {

						$ride = new Ride ($ride['id']);
						
						// Only display rides accepting bike types matching connected user's registered bikes
						if (!(isset($_POST['filter_bike']) AND !getConnectedUser()->checkIfAcceptedBikesMatches($ride))) {
						
							// Only display 'Friends only' rides if connected user is on the ride author's friends list
							if ($ride->privacy != 'Friends only' OR ($ride->privacy == 'Friends only' AND ($ride->author_id == getConnectedUser()->id OR $ride->getAuthor()->isFriend(getConnectedUser())))) {

								$is_ride = true; // Set "is_ride" variable to true as long as one ride to display has been found 

								include '../includes/rides/card.php';
						
							}
						}
					
					}
							
					if ($getResultsNumber->rowCount() > $limit) {
				
						// Set pagination system
						if (isset($_GET['p'])) $p = $_GET['p'];
						else $p = 1;
						$url = strtok($_SERVER["REQUEST_URI"], '?');
						$total_pages = ceil($getResultsNumber->rowCount() / $limit);
						
						// Build pagination menu
						include '../includes/pagination.php';

					}
					
				} ?>

			</div> <?php
			
			// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
			if (!isset($is_ride)) $errormessage = '表示できるデータがありません。';

			// If no bike is displaying, filter bike is checked and connected user doesn't have any bike set, display a message advising to register bikes
			if (isset($errormessage) AND $errormessage == '表示できるデータがありません。' AND isset($_POST['filter_bike']) AND is_array(getConnectedUser()->getBikes())){
				$submessage = '<a href="/rider/' .getConnectedUser()->id. '#addBike1">プロフィール設定</a>でバイクを登録しましょう。';
			} ?>
			
			<?php // Space for error messages and submessage
			if (isset($errormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; 
			if (isset($submessage)) echo '<div class="fullwidth text-center"><p>' .$submessage. '</p></div>'; ?>
		</div>
		
	</div>
	
</body>
</html>

<script src="/scripts/rides/display-rides.js"></script>