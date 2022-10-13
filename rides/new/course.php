<?php // Set up previous page variable for the back button
$previous_page = $_GET['stage']-1; ?>

<div class="container smaller page">

	<!-- Page title -->
	<h1 class="text-center">New Ride</h1>
	<legend>Course infos</legend>

	<!-- Displays an error message if needed -->
	<?php if (isset($errormessage)) echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>'; ?>

	<!-- Course infos input method selection form -->
	<form id='form' class="container smaller inner" method="POST" action="<?php echo CFG_FORM_ACTION; ?>?stage=<?php echo CFG_STAGE_ID+1; ?>">
		<label class="form-label">Course setting method</label>
		<select id="formMethodSelect" class="form-select" name="method">
			<option <?php if (empty($_SESSION['forms'][CFG_STAGE_ID]['method'])) {echo 'selected';} ?> hidden disabled value="none">Choose a course setting method...</option>
			<option <?php if ($_SESSION['forms'][CFG_STAGE_ID]['method'] == 'pick') {echo 'selected';} ?> value="pick">By picking checkpoints on the map</option>
			<option <?php if ($_SESSION['forms'][CFG_STAGE_ID]['method'] == 'import') {echo 'selected';} ?> value="import" disabled>By importing a *.gpx file</option>
			<option <?php if ($_SESSION['forms'][CFG_STAGE_ID]['method'] == 'draw') {echo 'selected';} ?> value="draw">By selecting a route from my routes</option>
		</select>

		<!-- Pick method -->
		<div id="js-pick" style="display: none;">
			<div class="rd-course-map-container">
				<div class="rd-course-map" id="newPickMap" class="mb-3">
				</div>
			</div>
			<div class="rd-course-fields">
				<div class="mb-3 row g-2">
					<div class="col-md">
						<label class="form-label required">Distance</label>
						<span style="padding: 0px 20px;">
							<input type="hidden" name="distance-about" value="precise">
							<input type="checkbox" name="distance-about" id="distance-about" value="about" <?php if ($_SESSION['forms'][CFG_STAGE_ID]['distance-about'] == 'about') {echo 'checked';} ?>>
							<label class="form-label">About</label>
						</span>
						<input type="number" id="distance" class="form-control withunit" name="distance" value="<?php echo $_SESSION['forms'][CFG_STAGE_ID]['distance']; ?>"><div class="unit"><p>km</p></div>
					</div>
					<div class="col-md">
						<label class="form-label required">Terrain</label>
						<select class="form-select" id="terrain" name="terrain">
							<option <?php if($_SESSION['forms'][CFG_STAGE_ID]['terrain'] == '1') {echo 'selected';} ?> value="1">Flat</option>
							<option <?php if($_SESSION['forms'][CFG_STAGE_ID]['terrain'] == '2') {echo 'selected';} ?> value="2">Small hills</option>
							<option <?php if($_SESSION['forms'][CFG_STAGE_ID]['terrain'] == '3') {echo 'selected';} ?> value="3">Hills</option>
							<option <?php if($_SESSION['forms'][CFG_STAGE_ID]['terrain'] == '4') {echo 'selected';} ?> value="4">Mountains</option>
						</select>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label required">Course description</label>
					<textarea class="form-control" id="course-description" name="course-description"><?php echo $_SESSION['forms'][CFG_STAGE_ID]['course-description']; ?></textarea>
				</div>
				
				<div class="btn-container">
					<a href="<?= 'new.php?stage=' .$previous_page?>">
						<button type="button" class="btn button btnleft">Back</button>
					</a>	
					<button type="submit" id="next" class="btn button btnright btn-primary" name="next">Save & next</button>
				</div>
			</div>
		</div>

		<!-- Draw method -->
		<div id="js-draw" style="display: none;">
			<div class="rd-course-map-container">
				<div class="rd-course-map" id="newDrawMap" class="mb-3"></div>
				<div id="profileBox" class="rd-course-profile p-0">
					<canvas id="elevationProfile"></canvas>
				</div>
			</div>
			<div class="rd-course-fields"> <?php
				$routes = $connected_user->getRoutes(0, 100);
				// var_dump($routes); ?>				
				<div class="mb-3">
					<label class="form-label required">Choose from my routes</label>
					<select class="form-select" id="selectRoute" name="my-routes">
						<option selected hidden disabled value="none">Select a route...</option> <?php
						forEach ($routes as $route) { ?>
							<option value="<?= $route['id'] ?>"> <?php
								$posting_date = date_format(new Datetime($route['posting_date']), 'Y/m/d');
								echo $posting_date. ' - ' .$route['name']. ' (' .round($route['distance'], 1). 'km)' ?>
							</option> <?php
						} ?>
					</select>
				</div>
				<div class="mb-3 row g-2">
					<div class="col-md">
						<label class="form-label">Distance : <div class="rd-automatic-field" id="distanceDiv"></div></label>
					</div>
					<div class="col-md">
						<label class="form-label">Terrain : <div class="rd-automatic-field" id="terrainDiv"></div></label>
					</div>
				</div>
				<div class="mb-3">
					<label class="form-label required">Course description</label>
					<textarea class="form-control" id="courseDescriptionTextarea"><?php echo $_SESSION['forms'][CFG_STAGE_ID]['course-description']; ?></textarea>
				</div>
				
				<div class="btn-container">
					<a href="<?php echo 'new.php?stage=' .$previous_page?>">
						<button type="button" class="btn button btnleft">Back</button>
					</a>	
					<button type="submit" id="next" class="btn button btnright btn-primary" name="next">Save & next</button>
				</div>
			</div>
		</div>
	</form>
</div>

<script src="/map/vendor.js"></script>
<script type="module" src="/rides/new/course.js"></script>