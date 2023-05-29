<?php

use \SendGrid\Mail\Mail;

require '../actions/databaseAction.php';

define('TOKEN_LIMIT', mt_getrandmax());

if (isset($_POST['validate'])) {

	// Check if user completed all fields
	if (!empty($_POST['email']) AND !empty($_POST['login'])) {
        
		// User data
		$email = htmlspecialchars($_POST['email']);
		$login = htmlspecialchars($_POST['login']);
		
		// Check if user exists (if login is correct)
		$CheckIfUserExists = $db->prepare('SELECT id FROM users WHERE email = ?');
		$CheckIfUserExists->execute(array($email));
        if ($CheckIfUserExists->rowcount() > 0) {

            // Get user data from the database
            $id = $CheckIfUserExists->fetch(PDO::FETCH_COLUMN);
			$user = new User($id);
            
            // Check if filled username matches registered one
            if ($login == $user->login) {

                // Set token
                $token = rand(0, TOKEN_LIMIT);
                $expiration_date = new DateTime('tomorrow');
                $today = new DateTime();
                $checkIfTokenHasBeenIssued = $db->prepare("SELECT user_id FROM user_resetpassword_token WHERE user_id = ? AND expiration_date < (NOW() + INTERVAL 1 DAY)");
                $checkIfTokenHasBeenIssued->execute([$user->id]);
                if ($checkIfTokenHasBeenIssued->rowCount() > 0) {
                    $updateToken = $db->prepare("UPDATE user_resetpassword_token SET token = ?, expiration_date = ? WHERE user_id = ?");
                    $updateToken->execute([$token, $expiration_date->format('Y-m-d H:i:s'), $user->id]);
                } else {
                    $insertToken = $db->prepare("INSERT INTO user_resetpassword_token (user_id, token, expiration_date) VALUES (?, ?, ?)");
                    $insertToken->execute([$user->id, $token, $expiration_date->format('Y-m-d H:i:s')]);
                }

                // Send mail
                $email = new Mail();
                $email->setFrom(
                    'contact@cyclingfriends.co',
                    'CyclingFriends'
                );
                $email->setSubject('パスワードの再発行');
                $email->addTo($user->email);
                $email->addContent(
                    'text/html',
                    '<p>パスワードの再発行手続きを進めるためには、下記のURLにアクセスし、新しいパスワードを入力してください。</p>
                    <a href="' .$_SERVER['HTTP_ORIGIN']. '/account/reset-password/' .$token. '">パスワード再発行専用ページはこちら</a>
                    <p>※上記のURLの有効期限は当日中です。有効期限が過ぎると、再発行できなくなってしまいますので、ご注意ください。</p>'
                );
                $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
                $response = $sendgrid->send($email);

                $successmessage = 'パスワードを再発行する手続きのご案内を' .$user->email. '宛に送信させて頂きました。手順に従って、再発行手続きを進めてください。';

            } else $errormessage = "ご記入頂いたユーザーネームは一致していません。";

        } else $errormessage = "ご記入頂いたメールアドレスは登録されていません。";

    } else $errormessage = "全ての情報をご記入の上、再度お試しください。";
}