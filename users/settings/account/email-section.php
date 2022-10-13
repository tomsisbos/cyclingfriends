<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include $_SERVER["DOCUMENT_ROOT"]. '/includes/head.php';
include $_SERVER["DOCUMENT_ROOT"]. '/actions/users/securityAction.php';
	$user = getConnectedUserInfo();
include $_SERVER["DOCUMENT_ROOT"]. '/actions/users/settings/account/emailAction.php';
?>

<body>

	<?php // Navbar
	include $_SERVER["DOCUMENT_ROOT"]. '/includes/navbar.php'; ?>
	
	<!-- Space for error messages -->
	<?php if(isset($successmessage)){ echo '<div class="success-block mb-0 mt-0"><p class="success-message">' .$successmessage. '</p></div>'; } ?>
	<?php if(isset($errormessage)){ echo '<div class="error-block mb-0 mt-0"><p class="error-message">' .$errormessage. '</p></div>'; } ?>
	
	<div class="container d-flex gap end">	
	
		<?php // Settings sidebar
		include $_SERVER["DOCUMENT_ROOT"]. '/includes/users/settings/sidebar.php'; ?>
	
		<!-- Email section -->
		<form class="container d-flex flex-column" method="post">
		
			<h2>Change email</h2>
	
			<div class="tr-row gap-20">
				<div class="col form-floating">
					<input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email" value="<?php if(isset($_POST['email'])){echo $_POST['email']; }else{ echo $user['email']; } ?>">
					<label class="form-label" for="floatingInput">Email address</label>
				</div>
				<div class="col form-floating mb-3">
					<input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
					<label class="form-label" for="floatingPassword">Password</label>
				</div>
			</div>
			<div>
				<button type="submit" class="btn button btnright button-primary" name="email-submit">Change email</button>
			</div>
				
		</form>

	</div>
	
</body>