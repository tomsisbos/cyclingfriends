<?php
 
require '../actions/databaseAction.php';
 
// Signup form validation
if (isset($_POST['validate'])) {
	 
	 // Check if user completed all fields
	if (!empty($_POST['email'])) {
		
		// Check if email address is valid
		if (filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)) {
		 
			// Email data
			$email = htmlspecialchars($_POST['email']);
			
            // Check if email address is already used
            if (!alreadyRegistered($email)) {
                registerEmail($email);
                $successmessage = 'メールアドレスが登録されました！';
            } else $errormessage = 'このメールアドレスは既に登録されています。';
                
		} else $errormessage = "正しいフォーマットのメールアドレスをご記入ください。";
    } else $errormessage = "メールアドレスをご記入ください。";
}

function alreadyRegistered ($email) {
	require '../actions/databaseAction.php';
    $isEmailRegistered = $db->prepare('SELECT id FROM mailing_list WHERE email = ?');
    $isEmailRegistered->execute(array($email));
    if ($isEmailRegistered->rowCount() > 0) return true;
    else return false;
}

function registerEmail ($email) {
	require '../actions/databaseAction.php';
    $registerEmail = $db->prepare('INSERT INTO mailing_list (email) VALUES (?)');
    $registerEmail->execute(array($email));
}

?>