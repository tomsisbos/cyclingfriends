<?php
include $_SERVER["DOCUMENT_ROOT"]. '/actions/databaseAction.php';

	if(isset($_POST['email']) AND !empty($_POST['email'])){
		$user_email = htmlspecialchars($_POST['email']);
		$user_password = htmlspecialchars($_POST['password']);
			
		//Check if filled password matches registered password
		if(password_verify($user_password, $user['password'])){
					
			// Check if filled email format is valid
			if(filter_var($user_email, FILTER_VALIDATE_EMAIL)){
						
				$updateEmail = $db->prepare('UPDATE users SET email = ? WHERE id = ?');
				$updateEmail->execute(array($user_email, $_SESSION['id']));
				$successmessage = 'Your email address has correctly been updated !';
			}else{
				$errormessage = 'This is not a valid email address. Please try again.';
			}
		}else{
					
			$errormessage = 'Your password is not correct. Please try again.';
		}
	} ?>