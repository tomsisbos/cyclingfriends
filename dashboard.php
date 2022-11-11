<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/dashboard.css" /> <?php 

session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php'; ?>

<body>

<?php include 'includes/navbar.php'; ?>

<div class="main dashboard" id="infiniteScrollElement">

	<div class="sidebar-left sticky-sidebar">

		<!-- Viewed mkpoints panel -->
		<div class="dashboard-container"> <?php 
			define('VIEWED_MKPOINTS_LIMIT', 20); 
			include 'includes/dashboard/viewed-mkpoints-counter.php';
			include 'includes/dashboard/viewed-mkpoints-list.php';
			include 'includes/dashboard/viewed-mkpoints-stats.php'; ?>
		</div>

	</div>
	
	<div class="sidebar-main">

		<div class="dashboard-container"> <?php
			include 'includes/dashboard/recent-activities.php'; ?>
		</div>

	</div>

	<div class="sidebar-right sticky-sidebar">

		<!-- Next rides panel -->
		<div class="dashboard-container"> <?php 
			include 'includes/dashboard/next-rides.php';  ?>
		</div>

	</div>

</div>

<script src="/includes/dashboard/dashboard.js"></script>
	
</body>
</html>