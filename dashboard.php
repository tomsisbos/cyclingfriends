<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php';
?>

<body>

<?php

	$connected_user->updateViewedMkpoints();

?>

<?php include 'includes/navbar.php'; ?>

<!--Page container-->
	<div class="container end">
		<p><?php echo '$_SESSION : '; ?></p>
		<pre><?php print_r($_SESSION); ?></pre>
	</div>
	
</body>
</html>