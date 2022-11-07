<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/dashboard.css" /> <?php 

session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php'; ?>

<body>

<?php include 'includes/navbar.php'; ?>

<!-- Viewed mkpoints panel -->
<div class="container"> <?php 

	include 'includes/dashboard/viewed-mkpoints-counter.php'; 
	define('VIEWED_MKPOINTS_LIMIT', 20); ?>
	<div class="dashboard-block viewed-mkpoints-list"> <?php
		include 'includes/dashboard/viewed-mkpoints-list.php'; ?>
	</div> <?php
	include 'includes/dashboard/viewed-mkpoints-stats.php'; ?>

</div>
	
</body>
</html>