<?php


$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (!empty($data)) {
	 
	// Check if user completed all fields
	if (!empty($data['email']) AND !empty($data['username']) AND !empty($data['password'])) {
		
		// Check if email address is valid
		if (filter_var(htmlspecialchars($data['email']), FILTER_VALIDATE_EMAIL)) {
		 
			// User data
			$email    = htmlspecialchars($data['email']);
			$login    = htmlspecialchars($data['username']);
			$password = password_hash($data['password'], PASSWORD_DEFAULT);

			// Create a new user
			$user = new User();

			// Check if user already exists
			if ($user->checkIfLoginAlreadyExists($login)) echo json_encode(['error' => "このユーザーネームは既に登録されています。"]);
		
			else {
			
				// Check if email address is already used
				if ($user->checkIfEmailAlreadyExists($email)) echo json_encode(['error' => "このメールアドレスは既に使われています。"]);
				
				else {
					
					if ($user->checkPasswordStrength($data['password'])) {
				
						$user->register($email, $login, $password);

						$user->send4DigitsVerificationMail();

						echo json_encode(['success' => '登録のメールアドレスに確認用のメールを送信しました。次の画面にて、メール内に記載されている4桁の番号をご記入ください。']);	

					} else  echo json_encode(['error' => 'パスワードは8文字以上利用してください。']);
				}
			}
		} else echo json_encode(['error' => "正しい形式のメールアドレスをご記入ください。"]);
	} else  echo json_encode(['error' => "全ての情報をご記入の上、再度お試しください。"]);

} ?>