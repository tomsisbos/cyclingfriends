<?php

// Display propic ?>
<div id="propic"> <?php
	$user->displayPropic(250, 250, 20); ?>
</div> <?php

// Propic modal
$propic = $user->downloadPropic();

if (isset($propic['img'])) { ?>

	<div id="propicModal" class="modal">
		<span class="close cursor" onclick="closePropicModal()">&times;</span>
		<div class="modal-block">

			<div class="propicSlide">
				<img src="<?= 'data:image/jpeg;base64,' .base64_encode($propic['img']); ?>" style="width:100%">
			</div>
			
		</div>
	</div> <?php

} ?>

<script defer src="/scripts/riders/propic.js"></script>