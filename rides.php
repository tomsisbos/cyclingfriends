<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php';
?>

<body>

	<?php include 'includes/navbar.php'; ?>
	
	<h2 class="top-title">Public Rides</h2>
	
	<div class="container end">
	
		<?php // Filter options
		include 'includes/rides/filter-options.php'; 
		
		// Select rides from database according to filter queries
		include 'actions/rides/displayRidesAction.php'; 
		
		if ($getRides->rowCount() > 0) {
			while ($ride = $getRides->fetch()) {

				$ride = new Ride ($ride['id']);
				
				// Only display rides accepting bike types matching connected user's registered bikes
				if (!(isset($_POST['filter_bike']) AND !$connected_user->checkIfAcceptedBikesMatches($ride))) {
				
					// Only display 'Friends only' rides if connected user is on the ride author's friends list
					if ($ride->privacy != 'Friends only' OR ($ride->privacy == 'Friends only' AND ($ride->author == $connected_user OR $ride->author->isFriend($connected_user)))) {

						$is_ride = true; // Set "is_ride" variable to true as long as one ride to display has been found ?>
				
						<div class="rd-card" id="rd-card">
				
							<!-- Left container -->
							<div class="rd-container-left">
								<a href="<?= 'rides/ride.php?id=' .$ride->id;?>" class="fullwidth">
									<?php // Truncate ride name if more than 60 characters
									$ride_name_truncated = truncate($ride->name, 0, 25);
									$featuredImage = $ride->getFeaturedImage(); ?>
									<div class="rd-image" style="background-image: url(data:image/jpeg;base64,<?= $featuredImage['img']; ?>); background-color: lightgrey">
										<div class="<?php if($featuredImage){ echo 'rd-ride-name-shadow'; }else{ echo 'rd-ride-name'; }?>"><?= $ride_name_truncated; ?></div>
										<div class="<?php if($featuredImage){ echo 'rd-ride-date-shadow'; }else{ echo 'rd-ride-date'; }?>"><?= $ride->date; ?></div>
									</div>
								</a>
							</div>
					
							<!-- Center container --> 
							<div class="rd-container-center">
								<div class="rd-section-address">
									<span class="iconify" data-icon="gis:poi-map" data-width="20"></span>
									<div class="text">
										<p><strong><?= $ride->meeting_place; ?></strong><?= ' - ' .$ride->meeting_time; ?></p>
									</div>
								</div>
								<div class="rd-section-text">
									<p><?= $ride->getAcceptedLevelTags(). ' (' .$ride->getAcceptedBikesString(). ')'; ?></p>
									<div class="rd-distance">
										<p><strong>Distance : </strong><?php if ($ride->distance_about === 'about') { echo $ride->distance_about. ' '; } echo $ride->distance. 'km'; ?></p>
										<?php if ($ride->terrain == 1) {
											echo '<img src="\includes\media\flat.svg" />';
										} else if ($ride->terrain == 2) {
											echo '<img src="\includes\media\smallhills.svg" />';
										} else if ($ride->terrain == 3) {
											echo '<img src="\includes\media\hills.svg" />';
										} else if ($ride->terrain == 4) {
											echo '<img src="\includes\media\mountain.svg" />';
										} ?>
									</div>
								</div>
							</div>
					
							<!-- Right container -->
							<div class="rd-container-right">
								<div class="rd-section-organizer">
									<a href="<?= 'riders/profile.php?id=' .$ride->author->id; ?>">
										<?= $ride->author->displayPropic(60, 60, 60); ?>
									</a>
									<div class="rd-organizer">
										<div class="rd-login"><?= 'by <strong>@' .$ride->author->login. '</strong>'; ?></div>
										<?php if ($ride->privacy === 'Friends only') { ?>
											<p style="background-color: #ff5555" class="tag-light text-light">Friends only</p>
										<?php } else { ?>
											<div class="rd-stars"><?= '★★★★☆ (4.1)'; ?></div>
										<?php } ?>
									</div>
								</div>
								<div class="rd-section-entry" style="background-color: <?= colorStatus($ride->status)[0]; ?>;">
									<span style="vertical-align: -webkit-baseline-middle;">
										<?= '<strong>Entry : </strong>' .$ride->status;
										if($ride->entry_start > date('Y-m-d')){
											if(nbDaysLeftToDate($ride->entry_start) == 1){
												echo '<br><div class="xsmallfont">Opening tomorrow</div>';
											}else{
												echo '<br><div class="xsmallfont">' .nbDaysLeftToDate($ride->entry_start). ' days before opening</div>';
											}
										}else if($ride->entry_start <= date('Y-m-d') AND date('Y-m-d') <= $ride->entry_end){
											if(nbDaysLeftToDate($ride->date) == 0){
												echo '<br><div class="xsmallfont">Last day for entering</div>';
											}else if(nbDaysLeftToDate($ride->date) == 1){
												echo '<br><div class="xsmallfont">Entries ending tomorrow</div>';
											}else{
												echo '<br><div class="xsmallfont">' .nbDaysLeftToDate($ride->entry_end). ' days before closing</div>';
											}	
										}else if($ride->entry_end <= date('Y-m-d') AND date('Y-m-d') <= $ride->date){
											if(nbDaysLeftToDate($ride->date) == 0){
												echo '<br><div class="xsmallfont text-danger">Departing today</div>';
											}else if(nbDaysLeftToDate($ride->date) == 1){
												echo '<br><div class="xsmallfont">Departing tomorrow</div>';
											}else{
												echo '<br><div class="xsmallfont">' .nbDaysLeftToDate($ride->date). ' days before departing</div>';
											}	
										} ?>
									</span>
								</div>
							</div>
						
						</div> <?php
					}
				}
			
			}
		}
		
		// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
		if(!isset($is_ride)){
			$errormessage = 'There is no ride to display.';		
		} ?>
		
		<?php 
		// If no bike is displaying, filter bike is checked and connected user doesn't have any bike set, display a message advising to register bikes
		if(isset($errormessage) AND $errormessage == 'There is no ride to display.' AND isset($_POST['filter_bike']) AND !$connected_user->isBike(1) AND !$connected_user->isBike(2) AND !$connected_user->isBike(3)){
			$submessage = 'You should first register your bike in <a href="/riders/profile.php?id=' .$connected_user->id. '#addBike1">your profile settings</a>.';
		} ?>
		
		<?php // Space for error messages and submessage
		if(isset($errormessage)){
			echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; 
		} 
		if(isset($submessage)){
			echo '<div class="fullwidth text-center"><p>' .$submessage. '</p></div>'; 
		} ?>
	
</body>
</html>