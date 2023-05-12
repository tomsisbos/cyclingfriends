<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />

<body>

	<?php include '../includes/navbar.php'; ?>
	
	<div class="main">
	
		<h2 class="top-title">ライド掲示板</h2>
		
		<div class="container end">
		
			<?php // Filter options
			include '../includes/rides/filter-options.php';
		
			// Define offset and number of rides to query
			$limit = 6;
			if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
			else $offset = 0;
			
			// Select rides from database according to filter queries
			include '../actions/rides/displayAction.php'; 
			
			// Display ride cards
			include '../includes/rides/display-rides.php';
			
			// Set an error message if $is_ride variable have not been declared (meaning that no iteration of the loop have been performed)
			if (!isset($is_ride)) $errormessage = '表示できるデータがありません。';

			// If no bike is displaying, filter bike is checked and connected user doesn't have any bike set, display a message advising to register bikes
			if (isset($errormessage) AND $errormessage == '表示できるデータがありません。' AND isset($_POST['filter_bike']) AND is_array($connected_user->getBikes())){
				$submessage = '<a href="/rider/' .$connected_user->id. '#addBike1">プロフィール設定</a>でバイクを登録しましょう。';
			} ?>
			
			<?php // Space for error messages and submessage
			if (isset($errormessage)) echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$errormessage. '</p></div>'; 
			if (isset($submessage)) echo '<div class="fullwidth text-center"><p>' .$submessage. '</p></div>'; ?>
		</div>
		
	</div>
	
</body>
</html>