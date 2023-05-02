<?php
 
require '../actions/databaseAction.php';
 
// Signup form validation
if (isset($_POST['validate'])) {
	 
	 // Check if user completed all fields
	if (!empty($_POST['email']) AND !empty($_POST['login']) AND !empty($_POST['password'])) {
		
		// Check if email address is valid
		if (filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)) {
		 
			// User data
			$email    = htmlspecialchars($_POST['email']);
			$login    = htmlspecialchars($_POST['login']);
			$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

			// Create a new user
			$user = new User();

			// Check if user already exists
			if ($user->checkIfLoginAlreadyExists($login)) $errormessage = "このユーザーネームは既に登録されています。";
		
			else {
			
				// Check if email address is already used
				if ($user->checkIfEmailAlreadyExists($email)) $errormessage = "このメールアドレスは既に使われています。";
				
				else {
					
					if ($user->checkPasswordStrength($_POST['password'])) {
				
						$user->register($email, $login, $password);

						$user->setSession();

						$_SESSION['success'] = 'アカウントが無事に登録されました。';

						// Redirect authentified user to the Dashboard	
						header('location: /');	

					} else $errormessage = 'パスワードは8文字以上利用してください。';
				}
			}
		} else $errormessage = "正しい形式のメールアドレスをご記入ください。";
	} else $errormessage = "全ての情報をご記入の上、再度お試しください。";
} ?>