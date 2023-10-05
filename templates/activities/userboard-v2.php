<?php

include '../actions/users/initSession.php';
include '../includes/head.php';

$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/blobStorage.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/activity.css"> 
<link rel="stylesheet" href="/assets/css/activities.css" />
<link rel="stylesheet" href="/assets/css/loaders.css" />

<body> <?php

	// Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for error messages
		include '../includes/result-message.php'; ?>
		
		<div id="root" data-storageurl="<?= $blobClient->getPsrPrimaryUri()->__toString() ?>" data-connected-user-id="<?= getConnectedUser()->id ?>"></div>
	
	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <!-- Load React component -->
    <script type="module" src="/react/runtime.js"></script>
    <script type="module" src="/react/myactivities.js"></script>
	
</body>