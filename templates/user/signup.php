<!DOCTYPE html>
<html lang="en">

<?php
require '../actions/users/signupAction.php'; ?>

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body style="position: relative"> <?php

include '../includes/navbar.php'; ?>

<div class="main">
	
	<div class="with-background-img">
		<div class="container-fluid end connection-page with-background-flash">

			<form class="container smaller connection-container" method="post">
				
				<div class="js-scenery-info classy-title"></div>
		
				<?php if (isset($errormessage)) echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>'; ?>
			
				<div class="form-floating mb-3">
					<input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
					<label class="form-label" for="floatingInput">Email address</label>
				</div>
				<div class="form-floating mb-3">
					<input type="login" class="form-control" id="floatingInput" placeholder="Login" name="login">
					<label class="form-label" for="floatingInput">Username</label>
				</div>
				<div class="form-floating mb-3">
					<input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
					<label class="form-label" for="floatingPassword">Password</label>
				</div>
				
				<button type="submit" class="btn button button-primary fullwidth" name="validate">Sign up</button>
				
			</form>
		</div>
	</div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>