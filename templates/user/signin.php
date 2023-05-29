<?php

require '../actions/users/signinAction.php';
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

	<div class="container-fluid end connection-page with-background-flash">

		<form class="container smaller connection-container" method="post">

			<div class="js-scenery-info classy-title"></div>
		
			<div class="form-floating mb-3">
				<input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
				<label class="form-label" for="floatingInput">メールアドレス</label>
			</div>
			<div class="form-floating mb-3">
				<input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
				<label class="form-label" for="floatingPassword">パスワード</label>
			</div>
			
			<button type="submit" class="btn button fullwidth button-primary" name="validate">ログイン</button>

			<div class="mt-4 sign-link">
				<div><a href="<?= $router->generate('user-signup') ?>">アカウントの新規作成はこちら</a></div>
				<div><a href="<?= $router->generate('user-reset-password-application') ?>">パスワードを忘れた方はこちら</a></div>
			</div>
			
		</form>
	</div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>