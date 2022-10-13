<?php
include $_SERVER["DOCUMENT_ROOT"]. '/actions/databaseAction.php';

	if(isset($_POST['password-submit'])){
		if(!empty($_POST['current-password']) AND !empty($_POST['new-password'])){
			$current_password = htmlspecialchars($_POST['current-password']);
			$new_password = htmlspecialchars($_POST['new-password']);
			
			// Check if filled password matches registered password
			if(password_verify($current_password, $user['password'])){
				
				// Check if the new passport is different as the former one
				if($current_password != $new_password){
					
					// Check if password format is valid
					if(checkPasswordStrength($new_password)){
						
						// Hash new password
						$encrypted_password = password_hash($new_password, PASSWORD_DEFAULT);
						
						$updatePassword = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
						$updatePassword->execute(array($encrypted_password, $_SESSION['id']));
						$successmessage = 'Your password has correctly been updated !';
					}else{
						$errormessage = 'Your password must be at least 6 characters long.';
					}
				}else{
					$errormessage = 'Your new password is the same as the current one. It won\'t be updated.';
				}
			}else{
				$errormessage = 'The current password you entered is not correct. Please try again.';
			}
		}else{
			$errormessage = 'Your password field is empty. You need to fill in a password.';
		}
	} ?>