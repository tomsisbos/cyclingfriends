<?php
 
require '../actions/database.php';
 
// Signup form validation
if (isset($_POST['validate'])) {
	 
	// Check if user completed all fields
	if (!empty($_POST['email']) AND !empty($_POST['password'])) {
		 
		// User data
		$email = htmlspecialchars($_POST['email']);
		$password = htmlspecialchars($_POST['password']);
		
		// Check if user exists (if login is correct)
		$CheckIfUserExists = $db->prepare('SELECT * FROM users WHERE email = ?');
		$CheckIfUserExists->execute(array($email));
		
		if ($CheckIfUserExists->rowcount() > 0) {
			
			// Get user data from the database
			$userData = $CheckIfUserExists->fetch();
			
			// Check if filled password matches registered password
			if (password_verify($password, $userData['password'])) {
			
				// Authentify user and load his data into global variables

				$user = new User($userData['id']);

				// Check if account has been verified
				if ($user->isVerified()) {

					session_start();

					$user->setSession();

					// Redirect authentified user to the Dashboard
					if (!empty($params) && isset($params['url'])) header('location: ' .$params['url']);
					else header('location: /dashboard');
					exit();

				} else $errormessage = 'こちらのアカウントに登録されているメールアドレスがまだ確認されていないため、アカウント作成が完了していません。登録時（' .$user->inscription_date. '）にお送りした確認用の自動メール内に掲載しているURLをクリックして、アカウント作成を完了させてください。<br>自動メールが確認できていない場合は、<a href="' .$router->generate('user-verification-guidance'). '">こちら</a>をご確認ください。';
			} else $errormessage = "ご記入頂いたパスワードは一致していません。";
		} else $errormessage = "ご記入頂いたメールアドレスは登録されていません。";
	} else $errormessage = "全ての情報をご記入の上、再度お試しください。";
} ?>