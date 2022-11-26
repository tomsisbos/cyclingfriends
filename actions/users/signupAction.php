<?php
 
require '../actions/databaseAction.php';
 
// Signup form validation
if (isset($_POST['validate'])) {
	 
	 // Check if user completed all fields
	if(!empty($_POST['email']) AND !empty($_POST['login']) AND !empty($_POST['password'])) {
		
		// Check if email address is valid
		if(filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)){
		 
			// User data
			$email    = htmlspecialchars($_POST['email']);
			$login    = htmlspecialchars($_POST['login']);
			$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

			// Create a new user
			$user = new User;

			// Check if user already exists
			if ($user->checkIfLoginAlreadyExists($login)) {
			
				$errormessage = "This user name is already used.";
		
			}else{
			
				// Check if email address is already used
				if ($user->checkIfEmailAlreadyExists($email)) {
			
					$errormessage = "This email is already registered.";
			
				}else{
					
					if($user->checkPasswordStrength($password)){
				
						$user->register($email, $login, $password);

						$user->setSession();

						// Redirect authentified user to the Dashboard	
						header('location: /');	

					}else{
						$errormessage = 'Your password must be at least 6 characters long.';
					}
				}
			}
		}else{
			$errormessage = "Please fill up all required informations and try again.";
		}
	}else{
		$errormessage = "Please fill in a valid email address.";
	}	
 }
 
 ?>
 
 
 
		
		
