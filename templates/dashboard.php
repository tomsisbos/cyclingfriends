<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/dashboard.css" /> <?php

session_start();
include '../actions/users/securityAction.php';
require '../actions/databaseAction.php'; ?>

<body>

<?php include '../includes/navbar.php'; ?>

<div class="main dashboard" id="infiniteScrollElement">
	
	<div class="sidebar-main">

		<div class="dashboard-container"> <?php
			include '../includes/dashboard/thread.php'; ?>
		</div>

	</div>
	
	<div class="sidebar-right sticky-sidebar">
	
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

</div>

<script src="/scripts/dashboard/dashboard.js"></script>
	
</body>
</html>