<!DOCTYPE html>
<html lang="en">

<?php 

session_start();
include '../../includes/head.php';
include '../../actions/users/securityAction.php';
include '../../actions/riders/profile/propicAction.php';
include '../../actions/riders/profile/profileInfosAction.php';
?>

<body> <?php

	$user = $connected_user;
	
	include '../../includes/navbar.php';
	
	// Space for general error messages
	displayMessage(); ?>
	
	<div class="container d-flex flex-column gap bg-user">

		<!-- Top section -->
		<div class="tr-row gap nav">
			<div class="td-row"> <?php
			
				// Include profile picture
				include '../../includes/riders/profile/edit/propic-admin.php'; ?>

			</div>
			<div class="flex-column">
				<h1 class="title-with-subtitle js-login"><?= $connected_user->login; ?></h1>
				<div class="d-flex gap">
				<?php // Only display social links if filled
				// Twitter
				if (isset($connected_user->twitter) AND !empty($connected_user->twitter)) { ?>
					<a target="_blank" href="<?= $connected_user->twitter ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a> <?php
				} // Facebook
				if (isset($connected_user->facebook) AND !empty($connected_user->facebook)) { ?>
					<a target="_blank" href="<?= $connected_user->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a> <?php
				} // Instagram
				if (isset($connected_user->instagram) AND !empty($connected_user->instagram)) { ?>
					<a target="_blank" href="<?= $connected_user->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a> <?php
				} // Strava
				if (isset($connected_user->strava) AND !empty($connected_user->strava)) { ?>
					<a target="_blank" href="<?= $connected_user->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a> <?php
				} ?>
				</div>
			</div> <?php
			
			// Include buttons ?>
			<div class="td-row push gap-30">
				<a href="/riders/profile.php?id=<?= $connected_user->id ?>">
					<button class="button btn">
						Check my profile
					</button>
				</a>
			</div> <?php

			// Include social admin panel
			include '../../includes/riders/profile/edit/social-admin.php'; ?>

		</div>
	</div>
	
	<?php // Include friends
	include '../../includes/riders/profile/friends.php'; ?>
	
	<div class="container d-flex flex-column gap end">
	
		<div class="container gap-10"> <?php
			// Profile infos
			include '../../includes/riders/profile/edit/infos-admin.php'; ?>	
		</div> <?php
		
		// Include profile gallery
		include '../../includes/riders/profile/edit/gallery-admin.php';
			
		// Include bikes
		include '../../includes/riders/profile/edit/bikes-admin.php'; ?>	

	</div>
	
</body>

<script src="/assets/js/lightbox-script.js"></script>
<script src="/includes/riders/friends/friends.js"></script>
<script src="/includes/riders/profile/edit/infos-admin.js"></script>
<script src="/includes/riders/profile/gallery.js"></script>
<script src="/includes/riders/profile/edit/gallery-admin.js"></script>
<script src="/includes/riders/profile/edit/bikes-admin.js"></script>