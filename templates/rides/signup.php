<?php

include '../actions/rides/rideAction.php';
require '../actions/rides/rideSignupAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />

<body> <?php

	// If set as private and connected user does not have admin rights on this ride, redirect to the dashboard
	if ($ride->privacy == 'private' AND (!isset($_SESSION['auth']) OR $ride->author_id != getConnectedUser()->id)) header('Location: /');

	// If set as Friends only and connected user is not on the friends list on the ride author, redirect to the dashboard
	if ($ride->privacy == 'friends_only' AND (!isset($_SESSION['auth']) OR (isset($_SESSION['auth']) && $ride->author_id != getConnectedUser()->id AND !$ride->getAuthor()->isFriend(getConnectedUser())))) header('Location: /');

	include '../includes/navbar.php'; ?>
		
	<div class="main"> <?php
		
		// Space for general error messages
		include '../includes/result-message.php'; ?>
		
		<div class="container end">

			<form class="container smaller" method="post">

				<div class="mb-5">
					<h3>アカウント作成に必要な情報</h3>
				
					<div class="form-floating mb-3<?php if (!empty($_POST) && (!isset($_POST['email']) || empty($_POST['email']))) echo ' missing-field' ?>">
						<input type="email" name="email" class="form-control" id="floatingEmail" placeholder="Email"<?php if (isset($_POST['email'])) echo ' value="' .$_POST['email']. '"'?>>
						<label class="form-label" for="floatingEmail">メールアドレス</label>
					</div>
					<div class="form-floating mb-3<?php if (!empty($_POST) && (!isset($_POST['login']) || empty($_POST['login']))) echo ' missing-field' ?>">
						<input type="login" name="login" class="form-control" id="floatingLogin" placeholder="Login"<?php if (isset($_POST['login'])) echo ' value="' .$_POST['login']. '"'?>>
						<label class="form-label" for="floatingLogin">ユーザーネーム</label>
					</div>
					<div class="form-floating mb-3<?php if (!empty($_POST) && (!isset($_POST['password']) || empty($_POST['password']))) echo ' missing-field' ?>">
						<input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password"<?php if (isset($_POST['password'])) echo ' value="' .$_POST['password']. '"'?>>
						<label class="form-label" for="floatingPassword">パスワード</label>
					</div>
				</div>

				<div class="mb-5">
					<h3>エントリーに必要な情報</h3>

					<div class="d-flex justify-content-between">
						<div class="form-floating col-5 mb-3<?php if (!empty($_POST) && (!isset($_POST['last_name']) || empty($_POST['last_name']))) echo ' missing-field' ?>">
							<input name="last_name" type="text" id="floatingLastName" placeholder="姓" class="form-control"<?php if (isset($_POST['last_name'])) echo ' value="' .$_POST['last_name']. '"'?>>
							<label class="form-label" for="floatingLastName">姓</label>
						</div>
						<div class="form-floating col-5 mb-3<?php if (!empty($_POST) && (!isset($_POST['first_name']) || empty($_POST['first_name']))) echo ' missing-field' ?>">
							<input name="first_name" type="text" id="floatingFirstName" placeholder="名" class="form-control"<?php if (isset($_POST['first_name'])) echo ' value="' .$_POST['first_name']. '"'?>>
							<label class="form-label" for="floatingFirstName">名</label>
						</div>
					</div>
					<div class="form-floating mb-3<?php if (!empty($_POST) && (!isset($_POST['birthdate']) || empty($_POST['birthdate']))) echo ' missing-field' ?>">
						<input name="birthdate" type="date" class="form-control" id="floatingBirthdate" min="1900-1-1" max="<?php date('Y-m-d'); ?>"<?php if (isset($_POST['birthdate'])) echo ' value="' .$_POST['birthdate']. '"'?>>
						<label class="form-label" for="floatingBirthdate">生年月日</label>
					</div>
				</div>
				
				<button type="submit" class="btn button button-primary fullwidth" name="validate">アカウント作成</button>

				<div class="mt-4 text-center">
					<a href="<?= '/ride/' .$ride->id . $router->generate('user-signin') ?>">既にアカウントをお持ちの方はこちら</a>
				</div>
				
			</form>
		</div>
	</div>

</body>
</html>