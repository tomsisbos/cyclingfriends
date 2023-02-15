<?php // Set up previous page variable for the back button
$previous_page = intval($slug) - 1; ?>

<!--Displays the Ride Infos form-->
<form method="POST">

	<div class="container ride-header text-shadow">
		<!-- Page title -->
		<h1 class="text-center"><?= $_SESSION['forms']['1']['ride-name'];?></h1>
		<legend>サマリー</legend>

	</div> <?php 

	// Displays an error message if needed
	if (isset($errormessage)) {
		echo '<div class="container error-block"><p class="error-message">' .$errormessage. '</p></div>'; 
	} else if (isset($successmessage)) {
		echo '<div class="container success-block"><p class="success-message">' .$successmessage. '</p></div>';
	} ?>

	<div class="container"> <?php

		include '../actions/rides/convertIntToStringValuesAction.php'; ?>
		
		<!-- Displays the summary -->
		<h2>About the ride</h2>
		<div class="row">
			<div class="col">
				<p><strong>開催日 :</strong> <?= $_SESSION['forms']['1']['date'];?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>集合時間 :</strong> <?= $_SESSION['forms']['1']['meeting-time'];?></p>
			</div>
			<div class="col">
				<p><strong>集合場所 :</strong> <?= $_SESSION['forms']['2']['meetingplace']['geolocation']['city']. ' (' .$_SESSION['forms']['2']['meetingplace']['geolocation']['prefecture']. ')';?></p>
				<p><strong>解散場所 :</strong> <?= $_SESSION['forms']['2']['finishplace']['geolocation']['city']. ' (' .$_SESSION['forms']['2']['finishplace']['geolocation']['prefecture']. ')';?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>出発時間 :</strong> <?= $_SESSION['forms']['1']['departure-time']. " (finish around " .$_SESSION['forms']['1']['finish-time']. ")";?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>募集人数 :</strong> <?= $_SESSION['forms']['1']['nb-riders-min']. "人 から " .$_SESSION['forms']['1']['nb-riders-max']. "人 まで";?></p>
			</div>
		</div>
		<div class="row">
			<div class="col">
				<p><strong>レベル :</strong> <?= levelFromArray($_SESSION['forms']['1']['level']); ?></p>
			</div>
			<div class="col">
				<p><strong>参加可能のバイク :</strong> <?= bikesFromArray($_SESSION['forms']['1']['accepted-bikes']); ?></p>
			</div>
		</div>
		<div class="row">
			<div class="col text-justify">
				<p><?= nl2br($_SESSION['forms']['1']['ride-description']);?></p>
			</div>
		</div>

	</div>

	<!-- Checkpoints -->
	<div class="summary-checkpoints"> <?php
		$checkpoints = $_SESSION['forms']['2']['checkpoints'];
		$options = $_SESSION['forms']['2']['options'];
		for ($i = 0; $i < count($checkpoints); $i++) { ?>
			<div class="summary-checkpoint" id="<?= $i; ?>">
				<div class="summary-checkpoint-image"> <?php
					if (isset($checkpoints[$i]['img'])) { ?>
						<img src="<?= $checkpoints[$i]['img']; ?>"> <?php
					} else if (isset($checkpoints[$i]['url'])) { ?>		
						<img src="<?= $checkpoints[$i]['url']; ?>"> <?php
					} else { ?>		
						<img src="\media\default-photo-<?= rand(1,9); ?>.svg"> <?php
					}
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
						if (isset($checkpoints[$i]['name'])) echo $checkpoints[$i]['name'];
						else {
							if ($i === 0) echo 'Start';
							else if ($options['sf'] === false AND $i === (count($checkpoints) - 1)) echo 'Goal';
							else echo 'Checkpoint n°' .$i;
						}
						if (isset($checkpoints[$i]['distance'])) { ?>
							<span style="font-weight: normal"><?= ' - km ' .round($checkpoints[$i]['distance'], 1); ?></span> <?php
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
					if (isset($checkpoints[0]['img'])) { ?>
						<img src="<?= $checkpoints[0]['img']; ?>"> <?php
					} else { ?>		
						<img src="\media\default-photo-<?= rand(1,9); ?>.svg"> <?php
					} ?>
					<div class="summary-checkpoint-tag tag-goal">
						GOAL
					</div>
					<div class="summary-checkpoint-name"> <?php
						if (isset($checkpoints[0]['name'])) { 
							echo $checkpoints[0]['name'];
						} else {
							echo 'Goal';
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
			if (!empty($_SESSION['forms']['2']['distance'])) { ?>
				<p><strong>距離 :</strong> 
				<?php 
				if ($_SESSION['forms']['2']['distance-about'] == 'about') echo '約';
				echo $_SESSION['forms']['2']['distance']. "km - " .$_SESSION['forms']['2']['meetingplace']['geolocation']['city']. " から " .$_SESSION['forms']['2']['finishplace']['geolocation']['city']. " まで"; ?>
				</p> <?php
			} ?>
		</div>
		<div class="row">
			<p><strong>起伏 :</strong> <?php 
				if (isset($_SESSION['course']) AND $_SESSION['course']['method'] == 'draw') echo $_SESSION['course']['terrain'];
				else echo $terrain_value; ?>
			</p>
		</div>

		<div class="row">
			<div class="col text-justify">
				<p><?= nl2br($_SESSION['forms']['2']['course-description']);?></p>
			</div>
		</div>	
		
		<?php // Hide the Create Ride button if a success message is displayed (prevents from creating multiple entries in the database)
		if (!isset($successmessage)) { ?>
			<div>
				<a href="<?= $previous_page ?>">
					<button type="button" class="btn button btnleft">戻る</button>
				</a>
				<button type="submit" class="btn button btnright btn-success" name="validate">作成する</button> <?php
			} ?>
		</div>
	
	</div>
	
</form>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/scripts/rides/new/summary.js"></script>