<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
			
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<h2 class="top-title">スカウトリスト</h2>
		
		<!-- Upper section -->
		<div class="container"> <?php
		
			// Filter options
			include '../includes/riders/scouts/filter-options.php'; 
		
			// Define offset and number of scouts to query
			$limit = 20;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;
			
			// Select scouts from database according to filter queries
			include '../actions/riders/scouts/displayScoutsAction.php'; ?>
		
		</div>
		
		<div class="container">
			<h3>フォローしているユーザー</h3>
		</div>
		
		<div class="container end bg-white"> <?php 
		
			if ($getScoutsData->rowCount() > 0) {

				while ($scout = $getScoutsData->fetch()) {
					$rider = new User($scout['id']);
					include '../includes/riders/rider-card.php';
				}
				
				if ($getResultsNumber->rowCount() > $limit) {
			
					// Set pagination system
					if (isset($_GET['p'])) $p = $_GET['p'];
					else $p = 1;
					$url = strtok($_SERVER["REQUEST_URI"], '?');
					$total_pages = $getResultsNumber->rowCount() / $limit;
					
					// Build pagination menu
					include '../includes/pagination.php';

				}

			} else {
				
				$suberrormessage = '誰もフォローしていません。プロフィールページで「フォローする」ボタンにクリックし、ドンドン繋がっていきましょう。'; 
				if (isset($suberrormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$suberrormessage. '</p></div>';
			
			} ?>
			
		</div>
	
	</div>
	
</body>
</html>

<script src="/scripts/riders/friends.js"></script>