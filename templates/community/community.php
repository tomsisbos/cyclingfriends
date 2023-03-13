<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body> <?php
	
	include '../includes/navbar.php'; ?>
	
	<div class="main"> <?php
	
		// Space for error messages
		include '../includes/result-message.php'; ?>
		
		<h2 class="top-title">Community</h2>
		
		<?php /*<div class="container"> <?php
		
			// Filter options
			include 'includes/riders/neighbours/filter-options.php'; ?>
			
		</div> <?php*/ 

			// Select riders from database according to filter queries
			include '../actions/riders/displayRidersAction.php'; ?>
			
		<div class="container end bg-white"> <?php

			if ($getRiders->rowCount() > 0) {
				while ($rider = $getRiders->fetch()) {
					
					$rider = new User($rider['id']);
					include '../includes/riders/rider-card.php';

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