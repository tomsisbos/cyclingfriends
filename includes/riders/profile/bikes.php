<?php

if (!empty($user->getBikes())) { ?>

	<div>
		<h2 id="bikeTitle" class="d-inline-block"><?= $user->login?>'s <?php if (isset($user->getBikes()[1])) { echo 'bikes'; } else { echo 'bike'; } ?></h2>
		<button id="showBike" class="btn smallbutton mb-2 mx-4">Show</button>
		<div id="bikes" class="d-flex flex-column gap my-2 hide"> <?php
			
			forEach ($user->getBikes() as $bike) {
				$bike = new Bike ($bike['id']); ?>

				<div class="bike-container">
					<div class="col-4 bike-image-container">
						<?php $bike->displayImage(); ?>
					</div>
					<div class="col-8 bike-infos-container">
						<div><strong>Type : </strong><?= $bike->type ?></div><?php
						if (!empty($bike->model)) { ?>
							<div><strong>Model : </strong><?= $bike->model ?></div><?php } 
						if (!empty($bike->components)) { ?>
							<div><strong>Components : </strong><?= $bike->components ?></div><?php }
						if (!empty($bike->wheels)) { ?>
							<div><strong>Wheels : </strong><?= $bike->wheels ?></div><?php }
						if (!empty($bike->description)) { ?>
							<div class="mt-1"><?= $bike->description ?></div><?php
						} ?>
					</div>
				</div> <?php
			} ?>

		</div>
	</div> <?php

} ?>


<script src="/includes/riders/profile/bikes.js"></script>