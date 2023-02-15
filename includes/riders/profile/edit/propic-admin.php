<!-- Profile picture uploading form -->
<form id="propic-form" enctype="multipart/form-data" action="#" method="post">
	<label for="propicfile" class="image-modify-container">
		<?php $connected_user->getPropicElement(100, 100, 100); ?>
		<span class="image-modify iconify" data-icon="ic:baseline-add-a-photo" data-width="30" data-height="30"></span>
	</label>
	<input id="propicfile" class="hidden" type="file" name="propicfile" onchange="propicformautosubmit()" size=50 />
	<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
</form>

<script src="/scripts/riders/propic-admin.js"></script>