<?php

	// Check if user is already participating
	if (!$ride->isParticipating($connected_user)) {
		
		// If entries are open, display entry infos
		if ($ride->isOpen() == 'open') {
			
			// If ride is full, display a message
			if ($ride->isFull()) {
				echo '<div class="td-row element-30 push"><p class="bold text-danger">This ride is full ! Wait for someone to quit or try to participate next time.</p></div>';
			// Else, display Join button
			} else { ?>
				<div class="td-row push">
					<button id="join" class="btn button button-success box-shadow">Join</button>
				</div> <?php 
			}
			
		// If entries are not open, display a text message instead of button
		} else if ($ride->isOpen() == 'not yet') { ?>
			<div class="td-row element-30 <?php if (!is_null($checkpoints)) { echo 'tag-dark'; } ?> push"><p class="bold text-danger">Entries are not open yet ! You will be able to apply for this ride starting <?= $ride->entry_start ?>.</p></div>
		
		<?php } else if ($ride->isOpen() == 'closed') { ?>
			<div class="td-row element-30 <?php if (!is_null($checkpoints)) { echo 'tag-dark'; } ?> push"><p class="bold text-danger">Entries are now closed. Try to find <a href="/rides.php">another ride</a> to join !</p></div> 
		
		<?php } 
		
	// Else, display Quit button
	} else { ?>
		<div class="td-row push">
			<button id="rd-quit" class="btn button button-danger box-shadow">Quit</button>
		</div> <?php
	}
	
// Script ?>

<script src="/includes/rides/join.js"></script> <?php /*

	// If no accepted bike type matches with connected user's bike list
	if ($ride->isBikeAccepted($connected_user)) { ?>

		<script>
			var join = document.getElementById('join')
			var bikesList = <?php echo json_encode($ride->getAcceptedBikes()); ?>
			var currentPage = window.location.href.toString()
			
			join.addEventListener('click', () => {
				window.location.href = "/rides/ride.php?id=" + rideId + "&join=true"
			} )

			var quit = document.getElementById('rd-quit')
			var rideId = <?= json_encode($ride->id); ?>
			var currentPage = window.location.href.toString()
			
			quit.addEventListener('click', quitAction)
			
			function quitAction () {
				window.location.href = "/rides/ride.php?id=" + rideId + "&quit=true"
			}
		</script>
			
	<?php 
	} else { ?>
		<script>
			var join = document.getElementById('join')
			var bikesList = <?php echo json_encode($ride->getAcceptedBikesString()); ?>
			var currentPage = window.location.href.toString()
			
			join.addEventListener('click', async () => {
				var answer = await openConfirmationPopup ('This ride only accepts the following bike types : ' + bikesList + '. You don\'t have any of these registered in your bikes list. Do you still want to enter this ride ?')
				if (answer) {
					window.location.href = "/rides/ride.php?id=" + rideId + "&join=true"
				}
			} )
			
			var quit = document.getElementById('rd-quit')
			var rideId = <?= json_encode($ride->id); ?>
			var currentPage = window.location.href.toString()
			
			quit.addEventListener('click', () => {
				window.location.href = "/rides/ride.php?id=" + rideId + "&quit=true"
			} )
		</script>
	<?php } ?> */