<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main">
		
		<h2 class="top-title">Neighbours</h2>
		
		<div class="container">
		
			<!-- Filter options --->
			<?php // include 'includes/riders/neighbours/filter-options.php'; ?>
			
		</div> <?php 
		
			// Select riders from database according to filter queries
			include '../actions/riders/displayNeighboursAction.php'; ?>
			
		<div class="nbr-container container end bg-white"> <?php

			if ($getRiders->rowCount() > 0) {
				foreach ($riders as $rider) { ?>					
					<div class="nbr-card"> <?php
						include '../includes/riders/rider-card.php'; ?>
						<div class="nbr-infos">
							<div class="nbr-distance"><?= $rider->distance ?>km</div> - 
							<div class="nbr-city"><?= $rider->location->toString() ?></div>
						</div>
					</div> <?php
				}

			} else {
				
				$errormessage = 'There is no rider to display.';
				
				if (isset($errormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; 

			} ?>
			
		</div>
	
	</div>
	
</body>
</html>