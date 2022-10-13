<div class="container gap">
	<div class="col-12">
		<div class="mb-3 row g-2">
			<?php if(!empty($user->last_name OR $user->first_name)){ ?>
				<div class="col-md">
					<strong>Name : </strong><?= $user->last_name. ' ' .$user->first_name; ?>
				</div>
			<?php }if(!empty($user->gender)){ ?>
				<div class="col-md">
					<strong>Gender : </strong><?= $user->gender; ?>
				</div>
			<?php } ?>
			<div class="row g-2">
				<?php if(!empty($user->birthdate)){ ?>
					<div class="col-md">
						<strong>Age : </strong><?= $user->calculateAge(). ' years old'; ?>
					</div>
				<?php }if(!empty($user->place)){ ?>
					<div class="col-md">
						<strong>Place : </strong><?= $user->place; ?>
					</div>
				<?php } ?>
			</div>
			<div class="row g-2">
				<?php if(!empty($user->level)){ ?>
					<div class="col-md">
						<strong>Level : </strong><?= $user->level; ?>
					</div>
				<?php } ?>
				<div class="col-md">
					<strong>Inscription date : </strong><?= $user->inscription_date; ?>
				</div>
			</div>
			<?php if(!empty($user->description)){ ?>
				<div class="row g-2">
					<?= $user->description; ?>
				</div>
			<?php } ?>
		</div>
	</div>
</div>