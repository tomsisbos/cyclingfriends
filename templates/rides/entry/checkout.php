<?php

include '../actions/users/initPublicSession.php';
include '../actions/rides/ride.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/steps.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/stripe.css" />
<script src="https://js.stripe.com/v3/"></script>
<script src="/scripts/stripe/checkout.js" defer></script>

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

		<h3>手続き完了</h3>

		<p><?= $ride->name ?>へエントリー頂き、ありがとうございます！</p>
		<p>ご登録頂きましたメールアドレス宛にエントリー内容を送信させて頂きました。</p>

		<div class="mt-3 d-flex justify-content-evenly"><a href="<?= $router->generate('ride-single', ['ride_id' => $ride->id]) ?>"><button class="btn button">ツアーページへ戻る</button></div>
	
	</div>

</body>
</html>