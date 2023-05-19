<?php

include '../actions/users/initSessionAction.php';
require '../actions/databaseAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/dashboard.css" />

<body> <?php

include '../includes/navbar.php';

include '../actions/twitter/authentificationAction.php';

// Start guidance if poor user info is set
if ($connected_user->userInfoQuantitySet() < 20) echo '<script src="/scripts/helpers/dashboard/on-empty-profile.js"></script>'

// Display general guidance during beta testing period ?>
<script src="/scripts/helpers/beta/default-guidance.js"></script>

<div class="main" id="infiniteScrollElement"> <?php

    // Space for general error messages
    include '../includes/result-message.php'; ?>

	<div>

		<div class="dashboard-container"> <?php

			include '../includes/posts/board.php';

			include '../includes/dashboard/thread.php'; ?>

		</div>

	</div>

</div>

<script type="module" src="/scripts/dashboard/dashboard.js"></script>

</body>
</html>