<?php

include '../actions/users/initSessionAction.php';
require '../actions/databaseAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/dashboard.css" />

<body>

<?php include '../includes/navbar.php';

// Start guidance if poor user info is set
if ($connected_user->userInfoQuantitySet() < 20) echo '<script src="/scripts/helpers/dashboard/on-empty-profile.js"></script>'

// Display general guidance during beta testing period ?>
<script src="/scripts/helpers/beta/default-guidance.js"></script>

<div class="main dashboard" id="infiniteScrollElement">

	<div>

		<div class="dashboard-container"> <?php

			include '../includes/posts/board.php';

			include '../includes/dashboard/thread.php'; ?>

		</div>

		<?php /*
		<div class="sidebar-left sticky-sidebar">
			<!-- Next rides panel -->
			<div class="dashboard-container"> <?php 
				include '../includes/dashboard/next-rides.php';  ?>
			</div>

			<!-- Cleared mkpoints panel -->
			<div class="dashboard-container"> <?php 
				define('CLEARED_MKPOINTS_LIMIT', 8); 
				include '../includes/dashboard/cleared-mkpoints-counter.php';
				include '../includes/dashboard/cleared-mkpoints-list.php';
				include '../includes/dashboard/cleared-mkpoints-stats.php'; ?>
			</div>
		</div>
		*/ ?>

	</div>

</div>

<script type="module" src="/scripts/dashboard/dashboard.js"></script>

</body>
</html>