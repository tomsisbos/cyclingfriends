<!DOCTYPE html>
<html lang="en">

<?php 
include 'includes/head.php';
require 'actions/users/signinAction.php';
?>

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body>

<?php include 'includes/navbar.php'; ?>
	
<!--Page container-->
<div class="with-background-img">
	<div class="container-fluid end connection-page with-background-flash">

		<!--Displays the signup form-->
		<form class="container smaller connection-container" method="post">

			<div class="classy-title"></div>
		
			<!--Displays an error message if anything bad happens in signupAction.php-->
			<?php if (isset($errormessage)) echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>'; ?>
		
			<div class="form-floating mb-3">
				<input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
				<label class="form-label" for="floatingInput">Email address</label>
			</div>
			<div class="form-floating mb-3">
				<input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
				<label class="form-label" for="floatingPassword">Password</label>
			</div>
			
			<button type="submit" class="btn button fullwidth button-primary" name="validate">Sign In</button>
			
		</form>
	</div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>