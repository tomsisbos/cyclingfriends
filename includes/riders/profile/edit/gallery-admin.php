<div class="container margin-bottom container-admin">

<h2>My profile gallery</h2> <?php

include '../../includes/riders/profile/gallery.php'; ?>

	<?php
	if (isset($_GET)) ?>

	<form class="admin-panel d-flex" id="profileGalleryForm">		
		<div class="d-flex">
			<label for="uploadProfileGallery" class="btn button d-grid">Upload</label>
			<input id="uploadProfileGallery" type="file" class="hidden" name="file[]" size=50 multiple onchange="displayFilelist()" />
			<div id="fileList" class="filename mx-4"></div> <?php
			if (!empty($connected_user->getProfileGallery())) { ?>
				<button id="deleteProfileGallery" class="btn button">Delete</button> <?php
			} ?>
		</div>
	</form>
		
</div>