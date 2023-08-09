<?php

include '../actions/users/initPublicSession.php';
include '../actions/rides/ride.php';
include '../includes/head.php';
include '../actions/rides/entry/process.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/steps.css" />

<body> <?php

	include '../includes/navbar.php';

	// Display steps guidance
	$steps = [
		'エントリー',
		'決済',
		'完了'
	];
	$step = 3;
	include '../includes/rides/entry/steps.php'; ?>

	<div class="main container">
        
        エントリーが完了しました！メールを送信させて頂きました。（TEST）

	</div>

</body>
</html>