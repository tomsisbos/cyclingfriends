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

	$user = getConnectedUser();
	
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
					<h2 class="title-with-subtitle js-login"><?= getConnectedUser()->login; ?></h2>
					<div class="d-flex gap">
					<?php // Only display social links if filled
					// Twitter
					$twitter = getConnectedUser()->getTwitter(); 
					if ($twitter->isUserConnected()) {?>
						<a target="_blank" href="<?= $twitter->url ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a> <?php
					} // Facebook
					if (isset(getConnectedUser()->facebook) AND !empty(getConnectedUser()->facebook)) { ?>
						<a target="_blank" href="<?= getConnectedUser()->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a> <?php
					} // Instagram
					if (isset(getConnectedUser()->instagram) AND !empty(getConnectedUser()->instagram)) { ?>
						<a target="_blank" href="<?= getConnectedUser()->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a> <?php
					} // Strava
					if (isset(getConnectedUser()->strava) AND !empty(getConnectedUser()->strava)) { ?>
						<a target="_blank" href="<?= getConnectedUser()->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a> <?php
					} ?>
					</div>
				</div> <?php
				
				// Include buttons ?>
				<div class="td-row push gap-30">
					<a href="/rider/<?= getConnectedUser()->id ?>">
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
if (getConnectedUser()->userInfoQuantitySet() < 30 && empty($_POST)) echo '<script src="/scripts/helpers/profile/on-empty-profile.js"></script>' ?> 