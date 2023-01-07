<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="../assets/css/profile.css" />
<link rel="stylesheet" href="../assets/css/activity.css" />
<link rel="stylesheet" href="../assets/css/lightbox-style.css" />
<link rel="stylesheet" href="../assets/css/dashboard.css" /> <?php

session_start();
include '../actions/users/securityAction.php';
include '../actions/riders/profile/profileAction.php';
include '../actions/riders/profile/propicAction.php';
include '../actions/riders/profile/profileInfosAction.php'; ?>

<body> <?php

	include '../includes/navbar.php'; ?>

	<div class="main"> <?php
		
		// Space for general error messages
		displayMessage(); ?>
		
		<div class="container pf-header">

			<!-- Top section -->

			<h2 class="title-with-subtitle js-login"><?= $user->login; ?></h2>
			<div class="d-flex gap"> <?php
			
			// Only display social links if filled
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
			</div> <?php
			
			// Buttons ?>
			<div class="td-row push gap-30"> <?php
				if ($_SESSION['id'] != $user->id) { ?>
					<!--<button title="チャット機能は開発中" id="sendMessageButton" class="btn button" name="send_message" disabled>Send message</button>--> <?php
					$rider = $user;
					include '../includes/riders/friends/buttons.php';
					include '../includes/riders/profile/send-message.php'; 
				} else { ?>
					<a href="/profile/edit">
						<button class="button btn">
							編集
						</button>
					</a> <?php
				} ?>
			</div>

		</div>
		
		<div class="container container-thin d-flex gap-20 nav bg-friend"> <?php

			// Include friends list
			include '../includes/riders/profile/friends-list.php'; ?>

		</div>
		
		<div class="container margin-bottom"> 
			
			<!-- Profile infos -->
			<div class="pf-general-infos"> <?php 
				
				// Include profile picture ?>
				<div class="pf-propic"> <?php
					include '../includes/riders/profile/propic.php'; ?>
				</div>

				<div class="pf-infos">
					<div class="mb-3 row g-2"> <?php
						if (!empty($user->last_name OR $user->first_name)) { ?>
							<div class="col-md">
								<strong>姓名 : </strong><?= $user->last_name. ' ' .$user->first_name; ?>
							</div> <?php
						}
						if (!empty($user->gender)) { ?>
							<div class="col-md">
								<strong>性別 : </strong><?= $user->getGenderString(); ?>
							</div> <?php
						} ?>
						<div class="row g-2"> <?php 
							if (!empty($user->birthdate)) { ?>
								<div class="col-md">
									<strong>年齢 : </strong><?= $user->calculateAge(). '才'; ?>
								</div> <?php
							}
							if (!empty($user->location->city)) { ?>
								<div class="col-md">
									<strong>場所 : </strong><?= $user->location->toString(); ?>
								</div> <?php
							} ?>
						</div>
						<div class="row g-2"> <?php
							if (!empty($user->level)) { ?>
								<div class="col-md">
									<strong>レベル : </strong><div class="d-inline <?= 'tag-' .$user->colorLevel($user->level) ?>"><?= $user->getLevelString(); ?></div>
								</div> <?php
							} ?>
							<div class="col-md">
								<strong>登録日時 : </strong><?= $user->inscription_date; ?>
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
			
			$photos = $user->getLastActivityPhotos(5);
			if (!empty($photos)) { ?>
				<div class="pf-photos-container"> <?php
					foreach ($photos as $photo_id) {
						$photo = new ActivityPhoto($photo_id); ?>
						<a class="pf-photo" href="/activity/<?= $photo->activity_id ?>">
							<img src="data:<?= $photo->type ?>;base64,<?= $photo->blob ?>" />
						</a> <?php
					} ?>
				</div> <?php
			} ?>

		</div> <?php

		if (!empty($user->getBikes())) { ?>
			<div class="container margin-bottom d-flex flex-column gap"> <?php
				// Include bikes
				include '../includes/riders/profile/bikes.php'; ?>
			</div> <?php
		} ?>

		<div class="container margin-bottom p-0">
			<div class="profile-title-block">
				<h3>Latest activities</h3><div class="cleared-counter"><?= '(' . $user->getActivitiesNumber() . ')' ?></div>
			</div>

			<div class="acsm-list dashboard-block"> <?php
				$activities = $user->getActivities(0, 6);
				foreach ($activities as $activity) {
					$activity = new Activity($activity['id']);
					include '../includes/activities/small-card.php';
				} ?>
			</div>
		</div>
		
		<div class="container bg-transparent margin-bottom p-0 pf-columns-container">
			
			<div class="pf-column">
				<div class="d-flex flex-column gap"> <?php
					// Cleared mkpoints panel ?>
					<div class="profile-container"> <?php 
						define('CLEARED_MKPOINTS_LIMIT', 8); 
						include '../includes/riders/profile/cleared-mkpoints.php'; ?>
					</div>
				</div>
			</div>

			<div class="pf-column">
				<div class="d-flex flex-column gap"> <?php
					// Cleared cegments panel ?>
					<div class="profile-container"> <?php 
						define('CLEARED_SEGMENTS_LIMIT', 8); 
						include '../includes/riders/profile/cleared-segments.php'; ?>
					</div>
				</div>
			</div>

		</div>

	
	</div>
	
</body>

<script src="/assets/js/lightbox-script.js"></script>
<script src="/scripts/riders/friends.js"></script>
<script src="/assets/js/friends-list.js"></script>
<!--<script src="/scripts/riders/gallery.js"></script>-->
<script src="/assets/js/send-message.js"></script>	