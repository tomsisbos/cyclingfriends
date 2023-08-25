<?php

include '../actions/users/initSession.php';
require '../actions/database.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/dashboard.css" />
<link rel="stylesheet" href="/assets/css/posts.css" />

<body> <?php // Compatible with class="black-theme"

include '../includes/navbar.php';
include '../includes/result-message.php';

$folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require $folder . '/actions/blobStorage.php';

// Display general guidance during beta testing period 
echo '<script src="/scripts/helpers/beta/default-guidance.js"></script>';

// Start guidance if poor user info is set
if (getConnectedUser()->userInfoQuantitySet() < 20) echo '<script src="/scripts/helpers/dashboard/on-empty-profile.js"></script>'; ?>

<div class="bg-darkercontainer end">

	<div id="dashboard" data-storageurl="<?= $blobClient->getPsrPrimaryUri()->__toString() ?>"></div>

</div>

</body>
</html>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<!-- Load React component -->
<script type="module" src="../react/runtime.js"></script>
<script type="module" src="../react/dashboard.js"></script>