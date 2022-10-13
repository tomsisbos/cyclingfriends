
<div class="container smaller page">

	<!-- Page title -->
	<h1 class="text-center">New Ride</h1>

	<!--Displays the Ride Infos form-->
	<form class="container smaller inner" method="POST" action="<?php echo CFG_FORM_ACTION; ?>?stage=<?php echo CFG_STAGE_ID+1; ?>">

		<legend>Ride infos</legend>
		
		<!--Displays an error message if needed-->
		<?php if (isset($errormessage)) { echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>'; } ?>
		
		<div class="mb-3">
			<label class="form-label required">Ride name</label>
			<input type="text" class="form-control" name="ride-name" value="<?php echo $_SESSION['forms'][CFG_STAGE_ID]['ride-name']; ?>">
		</div>
		<div class="mb-3">
			<label class="form-label required">Date</label>
			<input type="date" class="form-control" name="date" value="<?php echo $_SESSION['forms'][CFG_STAGE_ID]['date']; ?>" min="<?= date('Y-m-d'); ?>" max="2099-12-31">
		</div>
		<div class="mb-3 row g-2">
			<div class="col-md">
				<div class="form-floating">
					<input type="time" class="form-control" name="meeting-time" value="<?php echo $_SESSION['forms'][CFG_STAGE_ID]['meeting-time']; ?>">
					<label for="floatingInputGrid" class="required">Meeting time</label>
				</div>
			</div>
			<div class="col-md">
				<div class="form-floating">
					<input type="time" class="form-control" name="departure-time" value="<?php echo $_SESSION['forms'][CFG_STAGE_ID]['departure-time']; ?>">
					<label for="floatingInputGrid" class="required">Departure time</label>
				</div>
			</div>
		</div>
		<div class="mb-3 form-floating">
			<input type="time" class="form-control" name="finish-time" value="<?php echo $_SESSION['forms'][CFG_STAGE_ID]['finish-time']; ?>">
			<label for="floatingInputGrid" class="required">Finish time</label>
		</div>
		<div class="mb-3 row g-2">
			<div class="col-md">
				<div class="form-floating">
					<label class="form-label with-range-output required">Minimum number of riders</label>
					<input type="range" class="form-range" min="1" max="30" name="nb-riders-min" value="<?php
						if (empty($_SESSION['forms'][CFG_STAGE_ID]['nb-riders-min'])) echo 1;
						else echo $_SESSION['forms'][CFG_STAGE_ID]['nb-riders-min']; ?>"
						oninput="this.nextElementSibling.value = this.value">
						<output><?php
							if (empty($_SESSION['forms'][CFG_STAGE_ID]['nb-riders-min'])) echo 1;
							else echo $_SESSION['forms'][CFG_STAGE_ID]['nb-riders-min']; ?>
						</output>
				</div>
			</div>
			<div class="col-md">
				<div class="form-floating">
					<label class="form-label with-range-output required">Maximum number of riders</label>
					<input type="range" class="form-range" min="1" max="30" name="nb-riders-max" value="<?php
					if (empty($_SESSION['forms'][CFG_STAGE_ID]['nb-riders-max'])) echo 8;
					else echo $_SESSION['forms'][CFG_STAGE_ID]['nb-riders-max']; ?>" oninput="this.nextElementSibling.value = this.value">
					<output><?php
						if (empty($_SESSION['forms'][CFG_STAGE_ID]['nb-riders-max'])) echo 8;
						else echo $_SESSION['forms'][CFG_STAGE_ID]['nb-riders-max']; ?>
					</output>
				</div>
			</div>
		</div>
		<div class="mb-3 row g-2">
			<div class="col-md">
				<label class="form-label required">Level</label>
				<select class="form-select" name="level[]" multiple>
					<option value="1"<?php
						if (in_array(1, $_SESSION['forms'][CFG_STAGE_ID]['level'])) {echo ' selected="selected"';}
						?>>Beginner</option>
					<option value="2" <?php
						if (in_array(2, $_SESSION['forms'][CFG_STAGE_ID]['level'])) {echo ' selected="selected"';}
						?>>Intermediate</option>
					<option value="3" <?php
						if (in_array(3, $_SESSION['forms'][CFG_STAGE_ID]['level'])) {echo ' selected="selected"';}
						?>>Athlete</option>
				</select>
			</div>
			<div class="col-md">
				<label class="form-label required">Type of bikes accepted</label>
				<select class="form-select" name="accepted-bikes[]" multiple>
					<option value="1" <?php
						if (in_array(1, $_SESSION['forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
						?>>City bikes</option>
					<option value="2" <?php
						if (in_array(2, $_SESSION['forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
						?>>Road bikes</option>
					<option value="3" <?php
						if (in_array(3, $_SESSION['forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
						?>>Mountain bikes</option>
					<option value="4" <?php
						if (in_array(4, $_SESSION['forms'][CFG_STAGE_ID]['accepted-bikes'])) {echo ' selected="selected"';}
						?>>Gravel/Cyclocross bikes</option>
				</select>
			</div>
		</div>
		<div class="mb-3">
			<label class="form-label required">Ride description</label>
			<textarea class="form-control" name="ride-description"><?php echo $_SESSION['forms'][CFG_STAGE_ID]['ride-description']; ?></textarea>
		</div>
		
		
		<div class="btn-container">
			<button type="submit" class="btn button btnright btn-primary" name="next">Next</button>
		</div>	
			
	</form>

</div>