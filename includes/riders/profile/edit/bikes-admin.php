<div class="d-inline-block">
	<h2 class="d-inline-block">My bikes</h2>
	<button id="addBike" class="btn button mx-3">Add a bike</button>
</div>

<div id="bikes"> <?php

	forEach ($user->getBikes() as $bike) {
		$bike = new Bike ($bike['id']); ?>

		<div class="container container-admin js-bike-container d-flex flex-column gap" bike_id="<?= $bike->id ?>" >
			<div class="d-flex gap-20">
				<form title="Upload image" class="js-bike-image-form col-4" name="bike-image-form" enctype="multipart/form-data" method="post" action="..\actions\riders\profile\bikeImageAction.php">
					<div class="bike-image-container">
						<?php $bike->displayImage(); ?>
						<div class="image-icon-container">
							<label for="bikeImageFile<?= $bike->id ?>">
								<span class="image-icon iconify" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"></span>
							</label>
							<input id="bikeImageFile<?= $bike->id ?>" type="file" class="js-bike-input hidden" name="bikeimagefile" size=50 />
							<input type="hidden" name="MAX_FILE_SIZE" value="500000" />
							<input type="hidden" name="bike-id" value="<?= $bike->id ?>" />
							<div title="Delete bike" class="js-delete-bike" >
								<span class="image-icon iconify" data-icon="el:remove-circle" data-width="20" data-height="20"></span>
							</div>
						</div>
					</div>
					<div class="js-file-preview filename"></div>
				</form>

				<form method="post" class="fullwidth">
					<div class="row">
						<div class="col mb-3">
							<label><strong>Type : </strong></label>
							<select name="bike-type" class="js-bike-type admin-field">
								<option value="Other"<?php
									if ($bike->type == 'Other') { echo ' selected="selected"'; }
									?>>Other</option>
								<option value="City bike"<?php
									if ($bike->type == 'City bike') { echo ' selected="selected"'; }
									?>>City bike</option>
								<option value="Road bike"<?php
									if ($bike->type == 'Road bike') { echo ' selected="selected"'; }
									?>>Road bike</option>
								<option value="Mountain bike" <?php
									if ($bike->type == 'Mountain bike') { echo ' selected="selected"'; }
									?>>Mountain bike</option>
								<option value="Gravel/Cyclocross bike" <?php
									if ($bike->type == 'Gravel/Cyclocross bike') { echo ' selected="selected"'; }
									?>>Gravel/Cyclocross bike</option>
							</select>
						</div>
						<div class="col mb-3">
							<label><strong>Model : </strong></label>
							<input type="text" name="bike-model" class="js-bike-model admin-field" value="<?= $bike->model; ?>">
						</div>
					</div>
					<div class="row">
						<div class="col mb-3">
							<label><strong>Wheels : </strong></label>
							<input type="text" name="bike-wheels" class="js-bike-wheels admin-field" value="<?= $bike->wheels; ?>">
						</div>
						<div class="col mb-3">
							<label><strong>Components : </strong></label>
							<input type="text" name="bike-components" class="js-bike-components admin-field" value="<?= $bike->components; ?>">
						</div>
					</div>
					<div class="col mb-3">
						<label><strong>Description : </strong></label>
						<textarea name="bike-description" class="js-bike-description col-9 admin-field d-block fullwidth"><?php echo $bike->description; ?></textarea>
					</div>
				</form>
			</div>
			
		</div> <?php
	} ?>
</div>