<div class="d-inline-block">
	<h2 class="d-inline-block">バイク一覧</h2>
	<button id="addBike" class="btn button mx-3">バイクを追加</button>
</div>

<div id="bikes"> <?php
	forEach ($user->getBikes() as $bike) {
		$bike = new Bike ($bike); 
		include 'bike-admin-single.php';
		unset($bike); // To prevent influence on template
	} ?>
</div>

<template id="bikeTemplate"> <?php
	include 'bike-admin-single.php'; ?>
</template>