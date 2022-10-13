<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php';
	
	// Space for error messages
	if(isset($successmessage)){
		echo '<div class="success-block fullwidth m-0"><p class="success-message">' .$successmessage. '</p></div>'; 
	}
	if(isset($errormessage)){
		echo '<div class="error-block fullwidth m-0"><p class="error-message">' .$errormessage. '</p></div>'; 
	} ?>
	
	<h2 class="top-title">Friends</h2>
	
	<!-- Upper section -->
	<div class="container">
	
		<!-- Filter options --->
		<?php include '../includes/riders/friends/filter-options.php'; 
		
		// Select friends from database according to filter queries
		include '../actions/riders/friends/displayFriendsAction.php'; ?>
	
	</div>
	
		<!-- Friend requests --->
		<?php include '../includes/riders/friends/requests-list.php'; ?>
	
	<div class="container">
		<h3>Friends list</h2>
	</div>
	
	<?php include '../includes/riders/friends/friends-list.php'; ?> 
	
</body>
</html>

<script src="../includes/riders/friends/friends.js"></script>