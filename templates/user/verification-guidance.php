<?php

include '../actions/users/resendingVerificationEmail.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/home.css" />

<body class="black-theme"> <?php

	include '../includes/navbar.php'; ?>
		
	<div class="main"> <?php
                
        // Space for general error messages
        include '../includes/result-message.php'; ?>
		
		<div class="container home-container end"> <?php

			include '../includes/user/verification-guidance.php'; ?>

		</div>
	</div>

</body>
</html>