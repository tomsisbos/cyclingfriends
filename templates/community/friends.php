<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
	
		// Space for error messages
		displayMessage(); ?>
		
		<h2 class="top-title">友達リスト</h2>
		
		<!-- Upper section -->
		<div class="container"> <?php
		
			// Filter options
			include '../includes/riders/friends/filter-options.php'; ?>
		
		</div> <?php
		
			// Friend requests
			include '../includes/riders/friends/requests-list.php'; ?>
		
		<div class="container">
			<h3>友達リスト</h3>
		</div>
		
		<div class="container end bg-white"> <?php 
		
			// Define offset and number of friends to query
			$limit = 20;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;
			
			// Select friends from database according to filter queries
			include '../actions/riders/friends/displayFriendsAction.php';
		
			if ($getFriendsData->rowCount() > 0) {

				while ($friend = $getFriendsData->fetch()) {
					$rider = new User($friend['id']);
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
				
				$suberrormessage = '友達が見つかりませんでした。プロフィールページで「友達申請」ボタンにクリックし、友達を作りましょう。'; 
				if (isset($suberrormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$suberrormessage. '</p></div>';
			
			} ?>
			
		</div>
	
	</div>
	
</body>
</html>

<script src="/scripts/riders/friends.js"></script>