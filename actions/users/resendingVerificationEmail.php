<?php

if (isset($_POST) && !empty($_POST)) {

	 // Check if user completed all fields
	if (!empty($_POST['email']) AND !empty($_POST['login']) AND !empty($_POST['password'])) {
		
		// Check if email address is valid
		if (filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)) {
		 
			// User data
			$email    = htmlspecialchars($_POST['email']);
			$login    = htmlspecialchars($_POST['login']);
			$password = $_POST['password'];

			// Create a new user
			$user = new User();

			// Check if user already exists
			if ($user->checkIfLoginAlreadyExists($login)) {
			
				// Check if email address is already used
				if ($user->checkIfEmailAlreadyExists($email)) {
                    
                    $user_id = $user->getId($email);
                    $user = new User($user_id);

                    // Check if user is not already verified
                    if (!$user->isVerified()) {
					
                        // Check if password corresponds
                        if (password_verify($password, $user->getPassword())) {

                            $user->sendVerificationMail(false);

                            $_SESSION['successmessage'] = '登録のメールアドレスに確認用のメールを再送信しました。';

                        } else $errormessage = "ご記入頂いたパスワードは一致していません。";
                    } else $errormessage = 'このアカウントの作成は既に完了しています。<a href="' .$router->generate('user-signin'). '">ログインページ</a>にてアカウント情報を入力して頂ければ、ログインできます。';
				} else $errormessage = "ご入力いただいたメールアドレスと一致するユーザーデータは存在しません。";
			} else $errormessage = "ご入力いただいたユーザーネームと一致するユーザーデータは存在しません。";
		} else $errormessage = "正しい形式のメールアドレスをご記入ください。";
	} else $errormessage = "全ての情報をご記入の上、再度お試しください。";

}