<?php

include '../actions/users/initPublicSessionAction.php';
include '../actions/activities/getJournalIdAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/journal.css" />

<body>

	<?php // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for error messages
		include '../includes/result-message.php'; ?>
		
		<div id="journal" data-user="<?= $user_id ?>"></div>
	
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Load React component -->
    <script type="module" src="../react/runtime.js"></script>
    <script type="module" src="../react/journal.js"></script>
	
</body>