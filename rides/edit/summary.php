<?php // Set up previous page variable for the back button
$previous_page = $_GET['stage']-1; ?>

<!--Displays the Ride Infos form-->
<form method="POST">

	<div class="container ride-header text-shadow">
		<!-- Page title -->
		<h1 class="text-center"><?= $_SESSION['edit-forms']['1']['ride-name'];?></h1>
		<legend>Summary</legend>

	</div>

	<div class="container">
		
		<?php 
		// Displays an error message if needed
			if (isset($errormessage)) {
				echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>'; 
			} else if (isset($successmessage)) {
				echo '<div class="success-block"><p class="success-message">' .$successmessage. '</p></div>';
			}
		
		include '../actions/rides/convertIntToStringValuesAction.php'; ?>
		
		<!-- Displays the summary -->
		<h2>About the ride</h2>
		<div class="row">
			<div class="col">
				<p><strong>Date :</strong> <?= $_SESSION['edit-forms']['1']['date'];?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>Meeting time :</strong> <?= $_SESSION['edit-forms']['1']['meeting-time'];?></p>
			</div>
			<div class="col">
				<p><strong>Meeting place :</strong> <?= $_SESSION['edit-forms']['2']['meetingplace']['geolocation']['city']. ' (' .$_SESSION['edit-forms']['2']['meetingplace']['geolocation']['prefecture']. ')';?></p>
				<p><strong>Finish place :</strong> <?= $_SESSION['edit-forms']['2']['finishplace']['geolocation']['city']. ' (' .$_SESSION['edit-forms']['2']['finishplace']['geolocation']['prefecture']. ')';?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>Departure time :</strong> <?= $_SESSION['edit-forms']['1']['departure-time']. " (finish around " .$_SESSION['edit-forms']['1']['finish-time']. ")";?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>Number of riders wanted :</strong> <?= "from " .$_SESSION['edit-forms']['1']['nb-riders-min']. " to " .$_SESSION['edit-forms']['1']['nb-riders-max']. " people";?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>Level :</strong> <?= levelFromArray($_SESSION['edit-forms']['1']['level']); ?></p>
			</div>
			<div class="col">
				<p><strong>Accepted bikes :</strong> <?= bikesFromArray($_SESSION['edit-forms']['1']['accepted-bikes']); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col text-justify">
				<p><?= nl2br($_SESSION['edit-forms']['1']['ride-description']);?></p>
			</div>
		</div>

	</div>

	<!-- Checkpoints -->
	<div class="summary-checkpoints"> <?php
		$checkpoints = $_SESSION['edit-forms']['2']['checkpoints'];
		$options = $_SESSION['edit-forms']['2']['options'];
		for ($i = 0; $i < count($checkpoints); $i++) { ?>
			<div class="summary-checkpoint" id="<?= $i; ?>">
				<div class="summary-checkpoint-image"> <?php
					// Images that have been got from the database are stored in the 'blob' variable, images that have been uploaded during editing phase are uploaded in the 'img' entry with the display prefix already set.
					if (isset($checkpoints[$i]['img']['blob'])) { ?>
						<img src="data:<?= $checkpoints[$i]['img']['type'] ?>;base64,<?= $checkpoints[$i]['img']['blob'] ?>"> <?php
					} else if (isset($checkpoints[$i]['img']) AND is_string($checkpoints[$i]['img'])) { ?>
						<img src="<?= $checkpoints[$i]['img'] ?>"> <?php
					} else { ?> <img src="\includes\media\default-photo-<?= rand(1,9); ?>.svg"> <?php }
					if (($options['sf'] === true AND $i > 0 AND $i < (count($checkpoints))) OR ($options['sf'] === false AND $i > 0 AND $i < (count($checkpoints) - 1))) { ?>
						<div class="summary-checkpoint-number"> 
							<?= $i; ?>
						</div> <?php
					} else {
						if ($i === 0) { ?>
							<div class="summary-checkpoint-tag tag-start">
								<?= 'START' ?>
							</div> <?php
						} else if ($i === count($checkpoints) - 1) { ?>
							<div class="summary-checkpoint-tag tag-goal">
								<?= 'GOAL' ?>
							</div> <?php
						} 
					} ?>
					<div class="summary-checkpoint-name"> <?php
						if (isset($checkpoints[$i]['name']) && $checkpoints[0]['name'] != 'Checkpoint n°0' && $checkpoints[0]['name'] != 'Checkpoint n°'. (count($checkpoints) - 1)) echo $checkpoints[$i]['name'];
						else {
							if ($i === 0) echo 'Start';
							else if ($options['sf'] === false && $i === (count($checkpoints) - 1)) echo 'Goal';
							else echo 'Checkpoint n°' .$i;
						}
						if (isset($_SESSION['edit-forms']['2']['route-id'])) { ?>
							<span style="font-weight: normal"><?= ' - km ' .round($checkpoints[$i]['distance'], 1); ?></span> <?php
						} else { ?>
							<span style="font-weight: normal"><?= ' - alt. ' .round($checkpoints[$i]['elevation'], 1). 'm'; ?></span> <?php
						} ?>
					</div>
				</div> <?php
				if (!empty($checkpoints[$i]['description'])) { ?>
					<div class="summary-checkpoint-description">
						<?= $checkpoints[$i]['description'] ?>
					</div> <?php
				} ?>
			</div> <?php
			if ($options['sf'] === true OR ($options['sf'] === false AND $i != (count($checkpoints) - 1))) { ?>
				<svg height="120" width="10">
					<polygon points="0,00 10,60 0,120" />
				</svg> <?php
			}
		}
		if ($options['sf'] === true) { ?>
			<div class="summary-checkpoint">
				<div class="summary-checkpoint-image"> <?php
					// Images that have been got from the database are stored in the 'blob' variable, images that have been uploaded during editing phase are uploaded in the 'img' entry with the display prefix already set.
					if (isset($checkpoints[0]['img']['blob'])) { ?>
						<img src="data:<?= $checkpoints[0]['img']['type'] ?>;base64,<?= $checkpoints[0]['img']['blob'] ?>"> <?php
					} else if (isset($checkpoints[0]['img']) AND is_string($checkpoints[0]['img'])) { ?>
						<img src="<?= $checkpoints[0]['img'] ?>"> <?php
					} else { ?> <img src="\includes\media\default-photo-<?= rand(1,9); ?>.svg"> <?php } ?>
					<div class="summary-checkpoint-tag tag-goal">
						GOAL
					</div>
					<div class="summary-checkpoint-name"> <?php
						if (isset($checkpoints[0]['name']) && $checkpoints[0]['name'] != 'Checkpoint n°0' && $checkpoints[0]['name'] != 'Checkpoint n°'. (count($checkpoints) - 1)) { 
							echo $checkpoints[0]['name'];
						} else {
							echo 'Goal';
						}
						if (isset($_SESSION['edit-forms']['2']['route-id'])) { ?>
							<span style="font-weight: normal"><?= ' - km ' .round($_SESSION['edit-forms']['2']['distance'], 1); ?></span> <?php
						} else { ?>
							<span style="font-weight: normal"><?= ' - alt. ' .round($checkpoints[0]['elevation'], 1). 'm'; ?></span> <?php
						} ?>
					</div>
				</div> <?php
				if (!empty($checkpoints[0]['description'])) { ?>
					<div class="summary-checkpoint-description">
						<?= $checkpoints[0]['description'] ?>
					</div> <?php
				} ?>
			</div> <?php
		} ?>
	</div>

	<div class="container">

		<h2>About the course</h2>
		<div class="row"> <?php
			if (!empty($_SESSION['edit-forms']['2']['distance'])) { ?>
				<p><strong>Distance :</strong> 
				<?php 
				if ($_SESSION['edit-forms']['2']['distance-about'] == 'about') echo 'about ';
				echo $_SESSION['edit-forms']['2']['distance']. "km from " .$_SESSION['edit-forms']['2']['meetingplace']['geolocation']['city']. " to " .$_SESSION['edit-forms']['2']['finishplace']['geolocation']['city']; ?>
				</p> <?php
			} ?>
		</div>
		<div class="row">
			<p><strong>Terrain :</strong> <?php 
				if (isset($_SESSION['edit-course']) AND $_SESSION['edit-course']['method'] == 'draw') echo $_SESSION['edit-course']['terrain'];
				else echo $terrain_value; ?>
			</p>
		</div>


		<div class="row">
			<div class="col text-justify">
				<p><?= nl2br($_SESSION['edit-forms']['2']['course-description']);?></p>
			</div>
		</div>	
		
		<?php // Hide the Create Ride button if a success message is displayed (prevents from creating multiple entries in the database)
		if (!isset($successmessage)) { ?>
			<div>
				<a href="<?= 'edit.php?id=' . $_SESSION['edit-forms']['ride-id'] . '&stage=' .$previous_page?>">
					<button type="button" class="btn button btnleft">Back</button>
				</a>
				<button type="submit" class="btn button btnright btn-success" name="validate">Save changes</button>
		<?php }
		?>
		</div>
	
	</div>
	
</form>

<script src="/map/vendor.js"></script>
<script type="module" src="/rides/edit/summary.js"></script>