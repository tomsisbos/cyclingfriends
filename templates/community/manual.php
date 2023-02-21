<?php

include '../actions/users/initPublicSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<body>

	<?php include '../includes/navbar.php'; ?>

	<div class="main"> <?php
	
		// Space for error messages
		displayMessage(); ?>
		
		<h1 class="top-title">User manual</h2>
		
		<!-- Upper section -->
		<div class="container"> <?php

			include '../templates/manual/single.php';
			
			Manual::title(1, $title);

			Manual::intro($intro);
			
			?>
		</div>
	
	</div>
	
</body>
</html>