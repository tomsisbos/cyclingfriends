<div class="container container-admin">

	<form class="social-panel admin-panel justify flex" method="POST">	
		<!-- Displays social form -->
		<div class="col-6 text-right">
			<div class="col-md flex">
				<label><strong>Twitter : </strong></label>
				<input type="text" name="twitter" class="js-twitter admin-field" value="<?= $connected_user->twitter ?>">
			</div>
			<div class="col-md flex">
				<label><strong>Facebook : </strong></label>
				<input type="text" name="facebook" class="js-facebook admin-field" value="<?= $connected_user->facebook ?>">
			</div>
		</div>
		<div class="col-6 text-right">
			<div class="col-md flex">
				<label><strong>Instagram : </strong></label>
				<input type="text" name="instagram" class="js-instagram admin-field" value="<?= $connected_user->instagram ?>">
			</div>
			<div class="col-md flex">
				<label><strong>Strava : </strong></label>
				<input type="text" name="strava" class="js-strava admin-field" value="<?= $connected_user->strava ?>">
			</div>
		</div>
	</form>
		
</div>