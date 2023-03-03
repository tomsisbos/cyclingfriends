<?php

// Display propic ?>
<div id="propic"> <?php
	$user->getPropicElement(250, 250, 20); ?>
</div> <?php

// Propic modal

if (isset($propic['img'])) { ?>

	<div id="propicModal" class="modal">
		<span class="close cursor" onclick="closePropicModal()">&times;</span>
		<div class="modal-block">

			<div class="propicSlide">
				<img src="<?= $user->getPropicUrl() ?>" style="width:100%">
			</div>
			
		</div>
	</div> <?php

} ?>

<script defer type="module" src="/scripts/riders/propic.js"></script>