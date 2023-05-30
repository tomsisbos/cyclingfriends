<?php

// Display propic ?>
<div id="propic"> <?php
	$user->getPropicElement(250, 250, 20); ?>
</div> <?php

// Propic modal

if (isset($propic['img'])) { ?>

	<div id="propicModal" class="modal">
		<div class="modal-block">
			<span class="close cursor" onclick="closePropicModal()">&times;</span>
			<div class="img-slide">
				<img src="<?= $user->getPropicUrl() ?>">
			</div>
			
		</div>
	</div> <?php

} ?>

<script defer type="module" src="/scripts/riders/propic.js"></script>