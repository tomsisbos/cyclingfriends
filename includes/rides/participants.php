<!-- Include lightbox style --> 
<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

<?php
require '../actions/databaseAction.php';

$participation = $ride->getParticipants();

// Participants section
if (!empty($participation)) { ?>
	<div class="container container-thin bg-user">
		<div class="d-flex gap-20 nav">
			<h2 class="title-with-subtitle">Participants :</h2>
			<ul class="d-flex gap mb-0 p-0" id="participantsList"> <?php
				for ($i=0; $i < count($participation); $i++) {
					$participant = new User ($participation[$i]);
					echo $participant->displayPropic(60, 60, 60);
				} ?>
			</ul>
		</div>
		<?php // If ride is full, display a text message
		if ($ride->isFull()) {
			echo '<p class="text-danger mt-1 mb-0">This ride is full ! Wait for someone to quit or try to participate next time.</p>';
		} ?>
	</div> <?php
} ?>

<!-- Participants lightbox window -->
<div id="participantsWindow" class="modal modal-small" style="display: none;">
	<span class="close cursor" onclick="closeParticipantsWindow()">&times;</span>
	<div class="modal-block modal-block-thin">
		<div class="container bg-participant">
			<h2 class=""><?= $ride->name. "'s participants"; ?></h2>
		</div>
		<div class="container overflow-400">
			<div class="tr-row justify th-row bg-grey mb-2">
				<div class="td-row element-30">
				</div>
				<div class="td-row element-30">
					Login
				</div>
				<div class="td-row element-40">
					Place
				</div>
			</div>
			<?php
			if (!empty($participation)) {		
				foreach ($participation as $key => $participant_id) {
					$participant = new User ($participant_id); ?>
					<div class="tr-row justify">
						<div class="td-row element-30">
							<a style="text-decoration: none;" href="/riders/profile.php?id=<?= $participant->id; ?>"><?php $participant->displayPropic(60, 60, 60); ?></a>
						</div>
						<div class="td-row element-30">
							<?= $participant->login; ?>
						</div>
						<div class="td-row element-40">
							<?= $participant->place; ?>
						</div>
					</div> <?php
				}
			}else{
				echo 'No one have entered this ride yet';
			} ?>
		</div>
	</div>
</div>

<script src="/includes/rides/participants-list.js"></script>