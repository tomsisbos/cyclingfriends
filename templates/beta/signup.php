<?php

include '../actions/beta/signupAction.php';
require '../actions/users/signupAction.php';
include '../includes/head.php';

// If user has been created, update privatebeta members table
$member->updateUserId() ?>

<!DOCTYPE html>
<html lang="en">

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body style="position: relative"> <?php

	include '../includes/navbar.php'; ?>
		
	<div class="main with-background-img" data-parameter="public-scenery-imgs"> <?php
                
		if (isset($errormessage)) echo '<div class="error-block absolute"><p class="error-message">' .$errormessage. '</p></div>';
		if (isset($successmessage)) echo '<div class="success-block absolute"><p class="success-message">' .$successmessage. '</p></div>'; ?>
		
		<div class="container-fluid end connection-page with-background-flash">

			<form class="container smaller connection-container" method="post">
				
				<div class="js-scenery-info classy-title"></div>

                <div class="text-center text-white mb-4 small">
                    <?= $member->firstname ?>さん、CyclingFriendsの世界へようこそ！<br>
                    プライベートベータプログラムへの登録は完了しました。アカウントを作成して頂いたあと、<a href="/signin" target="_blank">こちら</a>のページよりプラットフォーム内にログインして頂けます。'
                </div>
			
				<input type="hidden" class="form-control" id="floatingInput" placeholder="Email" name="email" value="<?= $member->email ?>">

				<div class="form-floating mb-3">
					<input type="login" class="form-control" id="floatingInput" placeholder="Login" name="login">
					<label class="form-label" for="floatingInput">Username</label>
				</div>
				<div class="form-floating mb-3">
					<input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
					<label class="form-label" for="floatingPassword">Password</label>
				</div>
				
				<button type="submit" class="btn button button-primary fullwidth" name="validate">Sign up</button>
				
			</form>
		</div>
	</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>