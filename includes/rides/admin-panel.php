<div class="container margin-bottom container-admin">
	<h2>Administration panel</h2>
	<form class="admin-panel nav flex" enctype="multipart/form-data" method="POST">
		<div class="d-flex flex-column">
			<div>
				<label class="form-label">Privacy</label>
				<select class="admin-field" name="privacy" id="privacySelect">
					<option value="Private"<?php
						// First check if user has selected something and display it
						if (isset($_POST['privacy'])) {
							if ($_POST['privacy'] == 'Private') {
								echo ' selected';
							}
						// If user hasn't selected anything yet, check for existing data in ride table
						} else if (isset($ride->privacy)) {
							if ($ride->privacy == 'Private') {
								echo ' selected';
							}
						}
						?>>Private</option>
					<option value="Friends only"<?php // If some of the participants are not in ride author's friendlist
						if (!$ride->isEveryParticipantInFriendsList($ride->author)) {
							echo ' disabled';
						} 
						// First check if user has selected something and display it
						if (isset($_POST['privacy'])) {
							if ($_POST['privacy'] == 'Friends only') {
								echo ' selected';
							}
						// If user hasn't selected anything yet, check for existing data in ride table
						} else if (isset($ride->privacy)) {
							if ($ride->privacy == 'Friends only') {
								echo ' selected';
							}
						}
						?>>Friends only</option>
					<option value="Public"<?php
						// First check if user has selected something and display it
						if (isset($_POST['privacy'])) {
							if ($_POST['privacy'] == 'Public') {
								echo ' selected';
							}
						// If user hasn't selected anything yet, check for existing data in ride table
						} else if (isset($ride->privacy)) {
							if($ride->privacy == 'Public'){
								echo ' selected';
							}
						}
						?>>Public</option>
				</select>
			</div>
			
			<?php // If some of the participants are not in ride author's friendlist
			if (!$ride->isEveryParticipantInFriendsList($ride->author)) {
				// Display a warning message ?>
				<script src="/assets/js/friends-only-popup.js"></script><?php
			} ?>
			
			<?php // Set ride date -1j into a variable
			$oneDayBeforeRide = date('Y-m-d', strtotime($ride->date. ' - 1 days')); ?>
		
			<div>
				<label class="form-label">Entry period</label>
				<input type="date" class="admin-field" name="entry_start" value="<?php
				// First check if user has selected something and display it
				if (isset($_POST['entry_start'])) {
					echo $_POST['entry_start'];
				// If user hasn't selected anything yet, check for existing data in ride table
				} else if (isset($ride->entry_start)) {
					echo $ride->entry_start;
				// If there is on data in the table, set default date to current date
				} else {
					echo date('Y-m-d'); }?>" min="" max="<?= $oneDayBeforeRide; ?>">
				<input type="date" class="admin-field" name="entry_end" value="<?php
				// First check if user has selected something and display it
				if (isset($_POST['entry_end'])) {
					echo $_POST['entry_end'];
				// If user hasn't selected anything yet, check for existing data in ride table
				} else if (isset($ride->entry_end)) {
					echo $ride->entry_end;
				// If there is on data in the table, set default date to ride's previous day
				}else{
					echo date($oneDayBeforeRide); }?>" min="<?php if (isset($ride->entry_start)) { echo $ride->entry_start; } ?>" max="<?= $oneDayBeforeRide ?>">
			</div>
		</div>
		
		<div class="push flex-end">
			<button class="btn button" type="submit" name="save">Save changes</button>				
		</div>
	</form>
		
</div>