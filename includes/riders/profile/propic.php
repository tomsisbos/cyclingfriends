<?php

// Display propic ?>
<div id="propic"> <?php
	$user->displayPropic(100, 100, 100); ?>
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
	</div>

	<script>
	var propicModal = document.getElementById("propicModal")
	var modalBlock  = document.querySelector(".modal-block")

		document.querySelector('#propic').addEventListener('click', () => {
			
			propicModal.style.display = "block";
			
			// Close on clicking outside modal-block
			propicModal.onclick = function (e) {
				var eTarget = e ? e.target : event.srcElement
				if ((eTarget !== modalBlock) && (eTarget !== propicModal)){
					// Nothing
				}else{
					closePropicModal()
				}
			}
		} )
		
		function closePropicModal() {
			propicModal.style.display = "none";
		}
	</script> <?php

} ?>