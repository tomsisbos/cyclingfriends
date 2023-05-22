<?php

include '../actions/users/initSessionAction.php';
include '../actions/riders/profile/propicAction.php';
include '../actions/riders/profile/profileInfosAction.php';
include "../actions/riders/profile/bikeImageAction.php";
include '../actions/twitter/disconnectionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">
	
<link rel="stylesheet" href="/assets/css/profile.css" />
<link rel="stylesheet" href="/assets/css/lightbox-style.css" />

<body> <?php

	$user = $connected_user;
	
	include '../includes/navbar.php';
	
	// Space for general error messages
	include '../includes/result-message.php' ?>

	<div class="main">
	
		<div class="container d-flex flex-column gap bg-user">

			<!-- Top section -->
			<div class="tr-row gap nav">
				<div class="td-row"> <?php
				
					// Include profile picture
					include '../includes/riders/profile/edit/propic-admin.php'; ?>

				</div>
				<div class="flex-column">
					<h2 class="title-with-subtitle js-login"><?= $connected_user->login; ?></h2>
					<div class="d-flex gap">
					<?php // Only display social links if filled
					// Twitter
					$twitter = $connected_user->getTwitter(); 
					if ($twitter->isUserConnected()) {?>
						<a target="_blank" href="<?= $twitter->url ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a> <?php
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
					<a href="/rider/<?= $connected_user->id ?>">
						<button class="button btn">
							プロフィールを表示
						</button>
					</a>
				</div> <?php

				// Include social admin panel
				include '../includes/riders/profile/edit/social-admin.php'; ?>

			</div>
		</div>
		
		<div class="container d-flex flex-column gap end">
		
			<div class="container gap-10"> <?php
				// Profile infos
				include '../includes/riders/profile/edit/infos-admin.php'; ?>	
			</div> <?php
				
			// Include bikes
			include '../includes/riders/profile/edit/bikes-admin.php'; ?>	

		</div>

	</div>
	
</body>

<script src="/assets/js/lightbox-script.js"></script>
<script src="/scripts/riders/friends.js"></script>
<script type="module" src="/scripts/riders/infos-admin.js"></script>
<script type="module" src="/scripts/riders/bikes-admin.js"></script>
<script type="module" src="/scripts/riders/user-location-admin.js"></script> <?php
if ($connected_user->userInfoQuantitySet() < 30 && empty($_POST)) echo '<script src="/scripts/helpers/profile/on-empty-profile.js"></script>' ?> 