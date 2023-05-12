<?php

require '../actions/users/signupAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body class="black-theme relative"> <?php

	include '../includes/navbar.php'; ?>
		
	<div class="main with-background-img" data-parameter="public-scenery-imgs"> <?php
	
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container-fluid end connection-page with-background-flash">

			<form class="container smaller connection-container" method="post">
				
				<div class="js-scenery-info classy-title"></div>
			
				<div class="form-floating mb-3">
					<input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
					<label class="form-label" for="floatingInput">メールアドレス</label>
				</div>
				<div class="form-floating mb-3">
					<input type="login" class="form-control" id="floatingInput" placeholder="Login" name="login">
					<label class="form-label" for="floatingInput">ユーザーネーム</label>
				</div>
				<div class="form-floating mb-3">
					<input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
					<label class="form-label" for="floatingPassword">パスワード</label>
				</div>
				
				<button type="submit" class="btn button button-primary fullwidth" name="validate">アカウント作成</button>

                <div class="mt-4 sign-link">
                    <a href="<?= $router->generate('user-signin') ?>">既にアカウントをお持ちの方はこちら</a>
                </div>
				
			</form>
		</div>
	</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>