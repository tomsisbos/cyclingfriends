<?php

if (!empty($user->getBikes())) { ?>

	<div>
		<h2 id="bikeTitle" class="d-inline-block"><?= $user->login?>'s <?php if (isset($user->getBikes()[1])) { echo 'bikes'; } else { echo 'bike'; } ?></h2> <?php
		if (count($user->getBikes()) > 1) { ?>
			<button id="showBike" class="btn smallbutton mb-2 mx-4">他<?= count($user->getBikes()) - 1 ?>台を表示...</button> <?php
		} ?>
		<div class="d-flex flex-column gap my-2"> <?php
			
			forEach ($user->getBikes() as $bike_id) {
				$bike = new Bike ($bike_id); ?>

				<div class="pf-bike-container <?php if ($bike->number != 1) echo 'hidden' ?>">
					<div class="col-4 pf-bike-image-container">
						<?php $bike->displayImage(); ?>
					</div>
					<div class="col-8 pf-bike-infos-container">
						<div><strong>車種 : </strong><?= $bike->getType() ?></div><?php
						if (!empty($bike->model)) { ?>
							<div><strong>モデル : </strong><?= $bike->model ?></div><?php } 
						if (!empty($bike->components)) { ?>
							<div><strong>コンポネント : </strong><?= $bike->components ?></div><?php }
						if (!empty($bike->wheels)) { ?>
							<div><strong>ホイール : </strong><?= $bike->wheels ?></div><?php }
						if (!empty($bike->description)) { ?>
							<div class="mt-1"><?= $bike->description ?></div><?php
						} ?>
					</div>
				</div> <?php
			} ?>

		</div>
	</div> <?php

} ?>


<script type="module" src="/scripts/riders/bikes.js"></script>