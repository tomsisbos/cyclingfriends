<?php // Set up previous page variable for the back button
$previous_page = intval($stage_slug) - 1; ?>

<!--Displays the Ride Infos form-->
<form method="POST">

	<div class="container ride-header text-shadow">
		<!-- Page title -->
		<h1 class="text-center"><?= $_SESSION['edit-forms']['1']['ride-name'];?></h1>
		<legend>サマリー</legend>

	</div> <?php

	// Displays an error message if needed
	if (isset($errormessage)) {
		echo '<div class="container error-block"><p class="error-message">' .$errormessage. '</p></div>'; 
	} else if (isset($successmessage)) {
		echo '<div class="container success-block"><p class="success-message">' .$successmessage. '</p></div>';
	} ?>

	<div class="container">
		
		<!-- Displays the summary -->
		<h2>About the ride</h2>
		<div class="row">
			<div id="date" class="col">
				<p><strong>開催日 :</strong> <?= $_SESSION['edit-forms']['1']['date'];?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p id="meeting-time"><strong>集合時間 :</strong> <?= $_SESSION['edit-forms']['1']['meeting-time'];?></p>
			</div>
			<div class="col">
				<p id="meetingplace"><strong>集合場所 :</strong> <?= $_SESSION['edit-forms']['2']['meetingplace']['geolocation']['city']. ' (' .$_SESSION['edit-forms']['2']['meetingplace']['geolocation']['prefecture']. ')';?></p>
				<p id="finishplace"><strong>解散場所 :</strong> <?= $_SESSION['edit-forms']['2']['finishplace']['geolocation']['city']. ' (' .$_SESSION['edit-forms']['2']['finishplace']['geolocation']['prefecture']. ')';?></p>
			</div>
		</div>
		<div class="row">
			<div id="departure-time" class="col">
				<p><strong>出発時間 :</strong> <?= $_SESSION['edit-forms']['1']['departure-time']. " (finish around " .$_SESSION['edit-forms']['1']['finish-time']. ")";?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>募集人数 :</strong> <?= $_SESSION['edit-forms']['1']['nb-riders-min']. "人 から " .$_SESSION['edit-forms']['1']['nb-riders-max']. "人 まで";?></p>
			</div>
		</div>
		<div class="row">
			<div id="level" class="col">
				<p><strong>レベル :</strong> <?= levelFromArray($_SESSION['edit-forms']['1']['level']); ?></p>
			</div>
			<div class="col">
				<p><strong>参加可能のバイク :</strong> <?= bikesFromArray($_SESSION['edit-forms']['1']['accepted-bikes']); ?></p>
			</div>
		</div>
		<div class="row">
			<div id="ride-description" class="col text-justify">
				<p><strong>紹介文 :</strong><?= nl2br($_SESSION['edit-forms']['1']['ride-description']);?></p>
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
					// Treat both stored images and newly uploaded images
					if (isset($checkpoints[$i]['img']['filename'])) { ?>
						<img src="<?= $checkpoints[$i]['img']['url'] ?>"> <?php
					} else if (isset($checkpoints[$i]['img']) AND is_string($checkpoints[$i]['img'])) { ?>
						<img src="<?= $checkpoints[$i]['img'] ?>"> <?php
					// Images that have been imported through mkpoint
					} else if (isset($checkpoints[$i]['url'])) { ?>		
						<img src="<?= $checkpoints[$i]['url']; ?>"> <?php
					} else { ?> <img src="\media\default-photo-<?= rand(1,9); ?>.svg"> <?php }
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
					if (isset($checkpoints[0]['img']['filename'])) { ?>
						<img src="<?= $checkpoints[0]['img']['url'] ?>"> <?php
					} else if (isset($checkpoints[0]['img']) AND is_string($checkpoints[0]['img'])) { ?>
						<img src="<?= $checkpoints[0]['img'] ?>"> <?php
					} else { ?> <img src="\media\default-photo-<?= rand(1,9); ?>.svg"> <?php } ?>
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
							<span style="font-weight: normal"><?= ' - 標高 ' .round($checkpoints[0]['elevation'], 1). 'm'; ?></span> <?php
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
		<div id="distance" class="row"> <?php
			if (!empty($_SESSION['edit-forms']['2']['distance'])) { ?>
				<p><strong>距離 :</strong> 
				<?php 
				if ($_SESSION['edit-forms']['2']['distance-about'] == 'about') echo '約';
				echo $_SESSION['edit-forms']['2']['distance']. "km - " .$_SESSION['edit-forms']['2']['meetingplace']['geolocation']['city']. " から " .$_SESSION['edit-forms']['2']['finishplace']['geolocation']['city']. ' まで'; ?>
				</p> <?php
			} ?>
		</div>
		<div id="terrain" class="row">
			<p><strong>起伏 :</strong> <?php
				if (isset($_SESSION['edit-course']) AND $_SESSION['edit-course']['method'] == 'draw') echo $_SESSION['edit-course']['terrain'];
				else echo getTerrainFromValue($_SESSION['edit-forms']['2']['terrain']); ?>
			</p>
		</div>


		<div class="row">
			<div id="course-description" class="col text-justify">
				<p><strong>紹介文 :</strong><?= nl2br($_SESSION['edit-forms']['2']['course-description']);?></p>
			</div>
		</div> <?php
		
		// Hide the Create Ride button if a success message is displayed (prevents from creating multiple entries in the database)
		if (!isset($successmessage)) { ?>
			<div>
				<a href="<?= $previous_page?>">
					<button type="button" class="btn button btnleft">戻る</button>
				</a>
				<button type="submit" class="btn button btnright btn-success" name="validate">確定</button> 
			</div> <?php
		} ?>
	
	</div>
	
</form>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/scripts/rides/edit/summary.js"></script>