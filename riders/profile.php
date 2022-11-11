<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" /> <?php

session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
include '../actions/riders/profile/profileAction.php';
include '../actions/riders/profile/propicAction.php';
include '../actions/riders/profile/profileInfosAction.php'; ?>

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main"> <?php
		
		// Space for general error messages
		displayMessage(); ?>
		
		<div class="container d-flex flex-column gap bg-user">

			<!-- Top section -->
			<div class="tr-row gap nav">
				<div class="td-row"> <?php
				
					// Include profile picture
					include '../includes/riders/profile/propic.php'; ?>

				</div>
				<div class="flex-column">
					<h1 class="title-with-subtitle js-login"><?= $user->login; ?></h1>
					<div class="d-flex gap">
					<?php // Only display social links if filled
					// Twitter
					if (isset($user->twitter) AND !empty($user->twitter)) { ?>
						<a target="_blank" href="<?= $user->twitter ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a> <?php
					} // Facebook
					if (isset($user->facebook) AND !empty($user->facebook)) { ?>
						<a target="_blank" href="<?= $user->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a> <?php
					} // Instagram
					if (isset($user->instagram) AND !empty($user->instagram)) { ?>
						<a target="_blank" href="<?= $user->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a> <?php
					} // Strava
					if (isset($user->strava) AND !empty($user->strava)) { ?>
						<a target="_blank" href="<?= $user->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a> <?php
					} ?>
					</div>
				</div> <?php
				
				// Include buttons
				include '../includes/riders/profile/buttons.php'; ?>

			</div>
		</div> <?php

		// Include friends list
		include '../includes/riders/profile/friends-list.php'; ?>
		
		<div class="container d-flex flex-column gap end"> 
			
			<!-- Profile infos -->
			<div class="container gap">
				<div class="col-12">
					<div class="mb-3 row g-2"> <?php
						if (!empty($user->last_name OR $user->first_name)) { ?>
							<div class="col-md">
								<strong>Name : </strong><?= $user->last_name. ' ' .$user->first_name; ?>
							</div> <?php
						}
						if (!empty($user->gender)) { ?>
							<div class="col-md">
								<strong>Gender : </strong><?= $user->gender; ?>
							</div> <?php
						} ?>
						<div class="row g-2"> <?php 
							if (!empty($user->birthdate)) { ?>
								<div class="col-md">
									<strong>Age : </strong><?= $user->calculateAge(). ' years old'; ?>
								</div> <?php
							}
							if (!empty($user->place)) { ?>
								<div class="col-md">
									<strong>Place : </strong><?= $user->place; ?>
								</div> <?php
							} ?>
						</div>
						<div class="row g-2"> <?php
							if (!empty($user->level)) { ?>
								<div class="col-md">
									<strong>Level : </strong><?= $user->level; ?>
								</div> <?php
							} ?>
							<div class="col-md">
								<strong>Inscription date : </strong><?= $user->inscription_date; ?>
							</div>
						</div> <?php
						if (!empty($user->description)) { ?>
							<div class="row g-2">
								<?= $user->description; ?>
							</div> <?php
						} ?>
					</div>
				</div>
			</div> <?php
					
			// Include profile gallery
			include '../includes/riders/profile/gallery.php';

			// Include bikes
			include '../includes/riders/profile/bikes.php'; ?>	

		</div>
	
	</div>
	
</body>

<script src="/assets/js/lightbox-script.js"></script>
<script src="/assets/js/friends-list.js"></script>
<script src="/includes/riders/friends/friends.js"></script>
<script src="/includes/riders/profile/gallery.js"></script>
<script src="/assets/js/send-message.js"></script>	