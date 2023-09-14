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
	 
    // Check if token matches
	if (!empty($data['email']) AND !empty($data['digits'])) {

        $token = $data['digits'];
        $email = $data['email'];

        $checkToken = $db->prepare("SELECT u.id FROM tokens as t JOIN users as u ON t.user_id = u.id WHERE u.email = ? AND t.token = ?");
        $checkToken->execute([$email, $token]);

        // If token matches, verify account
        if ($checkToken->rowCount()) {

            $deleteToken = $db->prepare("DELETE FROM tokens WHERE token = ?");
            $deleteToken->execute([$token]);

            $user_id = $checkToken->fetch(PDO::FETCH_COLUMN);
            $user = new User($user_id);
            $user->verify();

            echo json_encode($user);

        } else echo json_encode(['error' => 'ご記入頂いた番号が一致しません。お手数ですが、ご確認の上、再度お試しください。']);
		
	} else echo json_encode(['error' => "全ての情報をご記入の上、再度お試しください。"]);

} ?>