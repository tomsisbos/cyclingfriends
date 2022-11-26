<?php

	// Check if user is already participating
	if (!$ride->isParticipating($connected_user)) {
		
		// If entries are open, display entry infos
		if ($ride->isOpen() == 'open') {
			
			// If ride is full, display a message
			if ($ride->isFull()) { ?>
				<p class="bold text-danger">This ride is full ! Wait for someone to quit or try to participate next time.</p> <?php
			// Else, display Join button
			} else { ?>
				<button id="join" class="btn button button-success box-shadow">Join</button> <?php 
			}
			
		// If entries are not open, display a text message instead of button
		} else if ($ride->isOpen() == 'not yet') { ?>
			<div class="tag-light"><div class="bold text-danger">Entries are not open yet ! You will be able to apply for this ride starting <?= $ride->entry_start ?>.</div></div> <?php
		} else if ($ride->isOpen() == 'closed') { ?>
			<div class="tag-light"><div class="bold text-danger">Entries are now closed. Try to find <a href="/rides">another ride</a> to join !</div></div> <?php
		} 
		
	// Else, display Quit button
	} else { ?>
		<button id="rd-quit" class="btn button button-danger box-shadow">Quit</button> <?php
	}
	
// Script ?>

<script src="/scripts/rides/join.js"></script> <?php /*

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