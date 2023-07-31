<?php

require '../actions/users/sendResetPasswordMail.php';
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

			<div class="classy-title">ユーザーネームとパスワードを下記のフォームにご記入ください。一致している場合は、メールアドレス宛に案内メールを送信させて頂きます。</div>
		
			<div class="form-floating mb-3">
				<input type="text" class="form-control" id="floatingLogin" placeholder="Username" name="login">
				<label class="form-label" for="floatingLogin">ユーザーネーム</label>
			</div>
			<div class="form-floating mb-3">
				<input type="email" class="form-control" id="floatingEmail" placeholder="Email" name="email">
				<label class="form-label" for="floatingEmail">メールアドレス</label>
			</div>
			
			<button type="submit" class="btn button fullwidth button-primary" name="validate">案内メールを送信</button>
			
		</form>
	</div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>