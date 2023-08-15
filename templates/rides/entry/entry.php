<?php

include '../actions/users/initPublicSession.php';
include '../actions/rides/ride.php';
include '../includes/head.php';
include '../actions/rides/entry/entry.php';
if (!getConnectedUser()) include '../actions/rides/rideSignup.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/steps.css" />
<link rel="stylesheet" href="/assets/css/amount.css" />

<body> <?php

	include '../includes/navbar.php';
    
    if (getConnectedUser()) $action = $router->generate('ride-payment', ['ride_id' => $ride->id]);
    else $action = '' ?>

	<div class="main">
        
        <form method="POST" action="<?= $action ?>" id="entry-form"> <?php

            // Space for general error messages
            include '../includes/result-message.php';

            // Display steps guidance
            if (!getConnectedUser()) $steps = [
                '登録',
                'エントリー',
                '決済',
                '完了'
            ];
            else $steps = [
                'エントリー',
                '決済',
                '完了'
            ];
            $step = 1;
            include '../includes/rides/entry/steps.php';

            // If guest user has created accound, display announce for mail validation
            if (!getConnectedUser() && isset($user->id)) include '../includes/rides/entry/waiting-for-verification.php';

            // On any other case, show entry form
            else {
                
                // Entry form
                if (getConnectedUser()) include '../includes/rides/entry/member-form.php';
                else include '../includes/rides/entry/guest-form.php';

                // Tours contract ?>
                <div class="container mt-3">
                    <h3>ツアー規約</h3>
                    <div class="popup-contract"><?php
                        include '../public/api/rides/contract.html'; ?>
                    </div>
                    <div class="form-floating justify-center d-flex gap mb-3">
                        <input type="checkbox" name="agreement" id="agreement" class="js-field">
                        <p class="required">ツアー規約をすべて読み、同意します</p>
                    </div>
                </div> <?php
                
                // Amount display
                if (getConnectedUser()) include '../includes/rides/entry/amount.php';
                
                // Submit button ?>
                <div class="container mt-3 d-flex gap"> <?php
                    if (getConnectedUser()) { ?>
                        <button type="submit" class="btn button" id="next">決済へ</button> <?php
                    } else { ?>
                        <a class="align-self-center fullwidth text-center" href="<?= $router->generate('user-signin-redirect', ['url' => $_SERVER['REQUEST_URI']]) ?>">既にアカウントをお持ちの方はこちら</a>
                        <button type="submit" class="btn button push flex-grow-0" id="next">アカウント作成</button> <?php
                    } ?>
                    <input type="hidden" name="validate" />
                </div> <?php

            } ?>            

        </form>

	</div>

</body>
</html>

<script src="\scripts\rides\entry.js"></script>