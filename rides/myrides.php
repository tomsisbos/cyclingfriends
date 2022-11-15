<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
// require '../actions/rides/myRidesAction.php';

// Clear session form data if ride already posted
if (isset($_SESSION['forms']['created'])) {
	unset($_SESSION['forms'][1]);
	unset($_SESSION['forms'][2]);
	unset($_SESSION['course']);
	$successmessage = "Your ride has been created ! It privacy is set as \"private\" for the moment, you need to change it to \"public\" if you want other riders to be able to apply.";
	unset($_SESSION['forms']['created']);
} ?>

<link rel="stylesheet" href="/assets/css/rides.css">

<body> <?php
	
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for general error messages
		displayMessage(); ?>
		
		<h2 class="top-title">My rides</h2>

		<div class="container end">
			<div class="my-rd-th justify">
				<div class="my-rd-td table-element e10 justify-center">
					Created on
				</div>
				<div class="my-rd-td table-element e10 justify-center">
					Start date
				</div>
				<div class="my-rd-td table-element e30">
					Ride name
				</div>
				<div class="my-rd-td table-element e15 justify-center">
					Status
				</div>
				<div class="my-rd-td table-element e10 justify-center">
					Applicants
				</div>
				<div class="my-rd-td table-element e10">
				</div>
			</div>
		
			<!-- Displays all my rides within a t-row with necessary infos data -->
			
			<?php 
			$rides = $connected_user->getRides();
			if (!empty($rides)) {
				forEach ($rides as $ride) {
					$ride = new Ride ($ride['id']); ?> 
				
					<div class="my-rd-tr justify">
						<div class="my-rd-td table-element e10 bg-white">
							<div class="my-rd-responsive-label">Created on : </div><?= $ride->posting_date ?>
						</div>
						<div class="my-rd-td table-element e10 bg-white">
						<div class="my-rd-responsive-label">Start date : </div><?= $ride->date ?>
						</div>
						<div class="my-rd-td table-element e30 bg-grey">
							<?= truncate($ride->name, 0, 50) ?>
						</div>
						<?php // Set text color depending on the status
						$status_color = colorStatus($ride->status)[0]; ?>
						<div class="my-rd-td table-element e15 text-center" style="background-color: <?= $status_color ?>;">
							<?= $ride->status;
							// Only add substatus if there is one
							if (!empty($ride->substatus)) { echo ' (' .$ride->substatus. ')'; } ?>
						</div>
						<?php $participation = $ride->setParticipationInfos() ?>
						<div class="my-rd-td table-element e10 bg-white my-rd-participants-number">
							<?= '<div><span style="color:' .$participation['participation_color']. '">' .$participation['participants_number']. '</span>&nbsp;/&nbsp;' .$ride->nb_riders_max. '</div> (min. ' .$ride->nb_riders_min. ')'; ?>
						</div>
						<div class="my-rd-td table-element e10 my-rd-button" style="padding-top: 0px; padding-bottom: 0px;">
							<a href="ride.php?id=<?= $ride->id ?>">
								<button class="btn button" type="button">check</button>
							</a>
						</div>
					</div> <?php
				}

			} else {
				$noride = 'You don\'t have admin rights on any ride !';
				if (isset($noride)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$noride. '</p></div>';
			} ?>
		</div>
	</div>
	
</body>
</html>
