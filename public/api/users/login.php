<?php


$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';
 
require '../actions/database.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if ($data) {
	 
	// Check if user completed all fields
	if (!empty($data['email']) AND !empty($data['password'])) {
		 
		// User data
		$email = htmlspecialchars($data['email']);
		$password = htmlspecialchars($data['password']);
		
		// Check if user exists (if login is correct)
		$CheckIfUserExists = $db->prepare('SELECT id, password FROM users WHERE email = ?');
		$CheckIfUserExists->execute(array($email));
		
		if ($CheckIfUserExists->rowcount() > 0) {
			
			// Get user data from the database
			$result = $CheckIfUserExists->fetch(PDO::FETCH_ASSOC);
			
			// Check if filled password matches registered password
			if (password_verify($password, $result['password'])) {
			
				// Authentify user and load his data into global variables

				$user = new User($result['id']);

				// Check if account has been verified
				if ($user->isVerified()) {

					echo json_encode($user);

				} else echo json_encode(['error' => 'こちらのアカウントに登録されているメールアドレスがまだ確認されていないため、アカウント作成が完了していません。登録時（' .$user->inscription_date. '）にお送りした確認用の自動メール内に掲載しているURLをクリックして、アカウント作成を完了させてください。<br>自動メールが確認できていない場合は、<a href="' .$router->generate('user-verification-guidance'). '">こちら</a>をご確認ください。']);
			} else echo json_encode(['error' => "ご記入頂いたパスワードは一致していません。"]);
		} else echo json_encode(['error' => "ご記入頂いたメールアドレスは登録されていません。"]);
	} else echo json_encode(['error' => "全ての情報をご記入の上、再度お試しください。"]);
} ?>