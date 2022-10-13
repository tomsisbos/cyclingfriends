<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

<?php 
session_start();
include '../../includes/head.php';
include '../../actions/users/securityAction.php';
?>

<body>

	<?php include '../../includes/navbar.php';
	
	// Space for error messages
	displayMessage(); ?>
	
	<div class="container-fluid mp-container">

		<div id="BuildRouteMap" class="mp-map"></div>

	</div>
	
</body>

<script src="/map/vendor.js"></script>
<script type="module" src="/map/class/CFUtils.js"></script>
<script type="module" src="/map/routes/new.js"></script>

</html>