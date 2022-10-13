<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include $_SERVER["DOCUMENT_ROOT"]. '/includes/head.php';
include $_SERVER["DOCUMENT_ROOT"]. '/actions/users/securityAction.php';
	$user = getConnectedUserInfo();
	$settings = getConnectedUserSettings();
include $_SERVER["DOCUMENT_ROOT"]. '/actions/users/settings/preferences/privacyAction.php';
?>

<body>

	<?php // Navbar
	include $_SERVER["DOCUMENT_ROOT"]. '/includes/navbar.php'; ?>
	
	<!-- Space for error messages -->
	<?php if(isset($successmessage)){ echo '<div class="success-block mb-0 mt-0"><p class="success-message">' .$successmessage. '</p></div>'; } ?>
	<?php if(isset($errormessage)){ echo '<div class="error-block mb-0 mt-0"><p class="error-message">' .$errormessage. '</p></div>'; } ?>
	
	<div class="container d-flex gap end">	
	
		<?php // Settings sidebar
		include $_SERVER["DOCUMENT_ROOT"]. '/includes/users/settings/sidebar.php'; ?>
	
		<!-- Privacy section -->
		<form class="container d-flex flex-column" method="post">
		
		<h2 class="mb-4">Privacy settings</h2>
	
			<div class="tr-row gap-20 mb-3">
				<div class="col-5">
					<label class="form-label" for="hide_on_riders_only">Hide to public on Riders page</label>
				</div>
				<div class="col-1">
					<input type="checkbox" name="hide_on_riders_only" <?php if((isset($_POST['hide_on_riders_only']) AND $_POST['hide_on_riders_only'] == 'on') OR (empty($_POST) AND isset($settings['hide_on_riders']))){echo 'checked';} ?>>
				</div>
			</div>
			<div class="tr-row gap-20 mb-3">
				<div class="col-5">
					<label class="form-label" for="hide_on_riders_neighbours">Hide to public on Riders and Neighbours page</label>
				</div>
				<div class="col-1">
					<input type="checkbox" name="hide_on_riders_neighbours" <?php if((isset($_POST['hide_on_riders_neighbours']) AND $_POST['hide_on_riders_neighbours'] == 'on') OR (empty($_POST) AND isset($settings['hide_on_neighbours']))){echo 'checked';} ?>>
				</div>
			</div>
			<div class="tr-row gap-20 mb-3">
				<div class="col-5">
					<label class="form-label" for="hide_on_chat">Do not accept messages except from friends</label>
				</div>
				<div class="col-1">
					<input type="checkbox" name="hide_on_chat" <?php if((isset($_POST['hide_on_chat']) AND $_POST['hide_on_chat'] == 'on') OR (empty($_POST) AND isset($settings['hide_on_chat']))){echo 'checked';} ?>>
				</div>
			</div>
			<div>
				<button type="submit" class="btn button btnright button-primary" name="privacy-submit">Save</button>
			</div>
				
		</form>

	</div>
	
</body>