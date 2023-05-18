<!-- Include lightbox style --> 
<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

<?php
require '../actions/databaseAction.php';

$participation = $ride->getParticipants();

// Participants section
if (!empty($participation)) { ?>
	<div class="container container-thin bg-user overflow-auto">
		<div class="d-flex gap-20 nav">
			<h3 class="title-with-subtitle">参加者 :</h3>
			<ul class="d-flex gap mb-0 p-0" id="participantsList"> <?php
				for ($i=0; $i < count($participation); $i++) {
					$participant = new User ($participation[$i]);
					echo $participant->getPropicElement(60, 60, 60);
				} ?>
			</ul>
		</div>
		<?php // If ride is full, display a text message
		if ($ride->isFull()) {
			echo '<p class="text-danger mt-1 mb-0">定員に達しました！次回のご参加をお待ちしております。</p>';
		} ?>
	</div> <?php
} ?>

<!-- Participants lightbox window -->
<div id="participantsWindow" class="modal modal-small" style="display: none;">
	<span class="close cursor" onclick="closeParticipantsWindow()">&times;</span>
	<div class="modal-block p-2">
		<div class="container bg-participant">
			<h3 class=""><?= $ride->name. "の参加者"; ?></h3>
		</div>
		<div class="small-rdr-cards-container container"> <?php
			if (!empty($participation)) {
				foreach ($participation as $key => $participant_id) {
					$rider = new User ($participant_id);
					include '../includes/riders/small-card.php';
				}
			} else echo '表示するデータはありません。'; ?>
		</div>
	</div>
</div>

<script src="/scripts/rides/participants-list.js"></script>