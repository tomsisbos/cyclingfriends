<div class="mb-3 row g-2">
	<div class="col">
		<label><strong>Name : </strong></label>
		<input name="last_name" type="text" id="floatingLastName" placeholder="Last name" class="js-last-name admin-field" value="<?= $user->last_name; ?>">
		<input name="first_name" type="text" id="floatingFirstName" placeholder="First name" class="js-first-name admin-field" value="<?= $user->first_name; ?>">
	</div>
	<div class="col">
		<label><strong>Gender : </strong></label>
		<select name="gender" class="js-gender admin-field">
			<option value="Undefined" <?php if (empty($user->gender)) { echo 'selected'; } ?>>Undefined</option>
			<option value="Man" <?php if ($user->gender == 'Man') { echo 'selected'; } ?>>Man</option>
			<option value="Woman" <?php if ($user->gender == 'Woman') { echo 'selected'; } ?>>Woman</option>
		</select>
	</div>
</div>
<div class="mb-3 row g-2">
	<div class="col">
		<label><strong>Birthdate : </strong></label>
		<input name="birthdate" type="date" class="js-birthdate admin-field" min="1900-1-1" max="<?php date('Y-m-d'); ?>" value="<?= $user->birthdate; ?>">
	</div>
	<div class="col">
		<label><strong>Place : </strong></label>
			<button id="userLocationButton" class="btn smallbutton">Pick on map</button>
			<div id="userLocationString"><?php if ($user->location->city) echo $user->location->toString() ?></div>
	</div>
</div>
<div class="mb-3 row g-2">
	<div class="col">
		<label><strong>Level : </strong></label>
		<select name="level" class="js-level admin-field">
			<option value="Beginner" <?php if ($user->level == 'Beginner') { echo 'selected'; } ?>>Beginner</option>
			<option value="Intermediate" <?php if ($user->level == 'Intermediate') { echo 'selected'; } ?>>Intermediate</option>
			<option value="Athlete" <?php if ($user->level == 'Athlete') { echo ' selected'; } ?>>Athlete</option>
		</select>
	</div>
	<div class="col">
		<strong>Inscription date : </strong>
		<?= $user->inscription_date ?>
	</div>
</div>
<div class="mb-3">
	<label><strong>Description : </strong></label>
	<textarea name="description" class="js-description admin-field col-12"><?= $user->description ?></textarea>
</div>