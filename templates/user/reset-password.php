<?php

require '../actions/users/resetPassword.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body class="black-theme"> <?php

include '../includes/navbar.php'; ?>
		
<div class="main with-background-img" data-parameter="public-scenery-imgs"> <?php
                
	include '../includes/result-message.php'; ?>

	<div class="container-fluid end connection-page with-background-flash"> <?php

        // Only display form if token is valid
        if (!isset($invalid_token)) { ?>

            <form class="container smaller connection-container" method="post">

                <div class="classy-title">下記のフォームに新しいパスワードをご記入ください。</div>
                
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
                    <label class="form-label" for="floatingPassword">新しいパスワード</label>
                </div>
                
                <button type="submit" class="btn button fullwidth button-primary" name="validate">確定</button>
                
            </form> <?php

        } ?>

	</div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>