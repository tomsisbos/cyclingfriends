<div class="d-inline-block">
	<h2 class="d-inline-block">My bikes</h2>
	<button id="addBike" class="btn button mx-3">Add a bike</button>
</div>

<div id="bikes"> <?php
	forEach ($user->getBikes() as $bike) {
		$bike = new Bike ($bike['id']); 
		include 'bike-admin-single.php';
		unset($bike); // To prevent influence on template
	} ?>
</div>

<template id="bikeTemplate"> <?php
	include 'bike-admin-single.php'; ?>
</template>