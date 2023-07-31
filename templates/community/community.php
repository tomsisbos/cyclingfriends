<?php

include '../actions/users/initSession.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body> <?php
	
	include '../includes/navbar.php'; ?>
	
	<div class="main"> <?php
	
		// Space for error messages
		include '../includes/result-message.php'; ?>
		
		<h2 class="top-title">ユーザー一覧</h2>
		
		<?php /*<div class="container"> <?php
		
			// Filter options
			include 'includes/riders/neighbours/filter-options.php'; ?>
			
		</div> <?php*/ 
		
			// Define offset and number of users to query
			$limit = 20;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;

			// Select riders from database according to filter queries
			include '../actions/riders/displayRiders.php'; ?>
			
		<div class="container end bg-white"> <?php

			if ($getRiders->rowCount() > 0) {

				while ($rider = $getRiders->fetch()) {
					
					$rider = new User($rider['id']);
					include '../includes/riders/rider-card.php';

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

			} else {
				
				$error = 'There is no rider to display.';
				
				if (isset($error)) {
					echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$error. '</p></div>'; 
				}
			}
			?>
			
		</div>
	</div>
	
</body>
</html>

<script src="/scripts/riders/friends.js"></script>