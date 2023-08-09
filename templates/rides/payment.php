<?php

include '../actions/users/initPublicSession.php';
include '../actions/rides/ride.php';
include '../includes/head.php';
include '../actions/rides/entry/treat.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/steps.css" />
<link rel="stylesheet" href="/assets/css/stripe.css" />
<script src="https://js.stripe.com/v3/"></script>
<script src="/scripts/stripe/checkout.js" defer></script>

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main">
        
        <form action="POST" id="payment-form" data-email="<?= $entry_data['email'] ?>"> <?php

            // Space for general error messages
            include '../includes/result-message.php';

            // Display steps guidance
            $steps = [
                'エントリー',
                '決済',
                '完了'
            ];
            $step = 2;
            include '../includes/rides/entry/steps.php'; ?>

            <div class="container mb-3">
                <h3>エントリー情報</h3>
                <p><strong>姓名：</strong><?= $entry_data['last_name'] .' '. $entry_data['first_name'] ?></p>
                <p><strong>生年月日：</strong><?= $entry_data['birthdate'] ?></p>
                <p><strong>メールアドレス：</strong><?= $entry_data['email'] ?></p> <?php
                foreach ($ride->getAdditionalFields() as $a_field) echo '<p><strong>' .$a_field->question. '：</strong>' .$entry_data['a_field_' .$a_field->id]; ?>
            </div>

            <div class="container mb-3">
                <h3>決済情報</h3>

                <div id="link-authentication-element"></div>

                <div id="payment-element"></div>

            </div>

            <div class="container mb-3">

                <div class="d-flex gap">
                    <a href="<?= $router->generate('ride-entry', ['ride_id' => $ride->id]) ?>"><div class="btn button d-flex justify-content-center">戻る</div></a>
                    <button class="btn button fullwidth" id="submit">
                        <div class="spinner hidden" id="spinner"></div>
                        <span id="button-text">決済して、上記の通りエントリーを確定する</span>
                    </button>
                </div>

                <div id="payment-message" class="hidden"></div>

            </div>

        </form>

	</div>

</body>
</html>