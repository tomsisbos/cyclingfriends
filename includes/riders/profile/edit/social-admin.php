<div class="container container-admin">

	<form class="social-panel admin-panel justify flex" method="POST">	
		<!-- Displays social form -->
		<div class="col-6 text-right">
			<div class="col-md flex gap align-items-center">
				<label><strong>Twitter : </strong></label> <?php
				$twitter = getConnectedUser()->getTwitter(); 
				if ($twitter->isUserConnected()) {
					echo ' @' .$twitter->username;
					include '../includes/twitter/disconnection-button.php';
				}
				else include '../includes/twitter/connection-button.php'; ?>
			</div>
			<div class="col-md flex gap align-items-center">
				<label><strong>Facebook : </strong></label>
				<input type="text" name="facebook" class="js-facebook admin-field" value="<?= getConnectedUser()->facebook ?>">
			</div>
		</div>
		<div class="col-6 text-right">
			<div class="col-md flex gap align-items-center">
				<label><strong>Instagram : </strong></label>
				<input type="text" name="instagram" class="js-instagram admin-field" value="<?= getConnectedUser()->instagram ?>">
			</div>
			<div class="col-md flex gap align-items-center">
				<label><strong>Strava : </strong></label>
				<input type="text" name="strava" class="js-strava admin-field" value="<?= getConnectedUser()->strava ?>">
			</div>
		</div>
	</form>
		
</div>