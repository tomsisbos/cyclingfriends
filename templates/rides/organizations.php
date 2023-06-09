<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en"> <?php 

// Clear session form data if ride already posted
if (isset($_SESSION['forms']['created'])) {
	unset($_SESSION['forms'][1]);
	unset($_SESSION['forms'][2]);
	unset($_SESSION['course']);
	$successmessage = "ライドページが作成されました！「非公開」に設定されているので、募集を開始する際にはライドページからプライバシー設定を変更しましょう。";
	unset($_SESSION['forms']['created']);
} ?>

<link rel="stylesheet" href="/assets/css/ride.css">

<body> <?php
	
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<h2 class="top-title">主催ライド一覧</h2>

		<div class="container end"> <?php
		
			// Define offset and number of rides to query
			$limit = 20;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;

			$rides = $connected_user->getRides($offset, $limit);

			if (!empty($rides)) {

				forEach ($rides as $ride) {
					$ride = new Ride ($ride['id']);
					
					include '../includes/rides/small-card.php';

				}
				
				if ($connected_user->getRidesNumber() > $limit) {
    
					// Set pagination system
					if (isset($_GET['p'])) $p = $_GET['p'];
					else $p = 1;
					$url = strtok($_SERVER["REQUEST_URI"], '?');
					$total_pages = ceil($connected_user->getRidesNumber() / $limit);
					
					// Build pagination menu
					include '../includes/pagination.php';
		
				}

			} else {
				$noride = 'あなたが管理しているライドがありません。';
				if (isset($noride)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$noride. '</p></div>';
			} ?>

		</div>
	</div>
	
</body>
</html>

<script src="/scripts/rides/delete.js" defer></script>
