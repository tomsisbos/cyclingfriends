<?php

include '../actions/users/initSession.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css">

<body> <?php
	
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<h2 class="top-title">参加ツアー一覧</h2>

		<div class="container end"> <?php
		
			// Define offset and number of rides to query
			$limit = 20;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;

			$rides = getConnectedUser()->getRideParticipations($offset, $limit);

			if (!empty($rides)) {

				forEach ($rides as $ride) {
					$ride = new Ride($ride['ride_id']);
					
					include '../includes/rides/small-card.php';

				}
				
				if (getConnectedUser()->getRideParticipationsNumber() > $limit) {
    
					// Set pagination system
					if (isset($_GET['p'])) $p = $_GET['p'];
					else $p = 1;
					$url = strtok($_SERVER["REQUEST_URI"], '?');
					$total_pages = ceil(getConnectedUser()->getRideParticipationsNumber() / $limit);
					
					// Build pagination menu
					include '../includes/pagination.php';
		
				}

			} else {
				$noride = 'あなたが参加しているツアーがありません。';
				if (isset($noride)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$noride. '</p></div>';
			} ?>

		</div>
	</div>
	
</body>
</html>

<script src="/scripts/rides/delete.js" defer></script>
