<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php';
?>

<body> <?php
	
	include 'includes/navbar.php'; ?>
	
	<div class="main"> <?php
	
		// Space for error messages
		displayMessage(); ?>
		
		<h2 class="top-title">Community</h2>
		
		<div class="container"> <?php
		
			// Filter options
			/// include 'includes/riders/neighbours/filter-options.php'; ?>
			
		</div> <?php 

			// Select riders from database according to filter queries
			include 'actions/riders/displayRidersAction.php'; ?>
			
		<div class="container end bg-white"> <?php

			if ($getRiders->rowCount() > 0) {
				while ($rider = $getRiders->fetch()) {
					
					$rider = new User ($rider['id']); ?>
		
					<div class="rdr-card <?php if ($connected_user->isFriend($rider)) { echo 'bg-friend'; } else { echo 'bg-rider'; } ?>">
							<div class="rdr-card-inner">
					
								<!-- Profile picture -->
								<div class="rdr-propic">
									<a href="/riders/profile.php?id=<?= $rider->id ?>"><?php $rider->displayPropic(80, 80, 80); ?></a>
								</div>
						
								<!-- Left container -->
								<div class="rdr-container-left">
									<a class="normal" href="/riders/profile.php?id=<?= $rider->id ?>">
										<div class="rdr-login-section"> <?php 
											if (!empty($rider->gender)) { ?>
												<div class="rdr-gender">	<?php
													echo getGenderAsIcon($rider->gender); ?>
												</div> <?php
												} ?>
											<div class="rdr-login js-login"><?= $rider->login; ?></div>
											<div class="rdr-name"><?php
												if (!empty($rider->last_name) AND !empty($rider->first_name)) {
													echo '- (' .strtoupper($rider->last_name);
												}
												if (!empty($rider->first_name)) {
													echo ' ' .ucfirst($rider->first_name. ')');
												} ?>
											</div>
										</div>
									</a>
									<div class="rdr-maininfos-section">
										<div class="rdr-sub">
											<div class="d-flex gap"> <?php 
												// Only display social links if filled
												if (isset($rider->twitter) AND !empty($rider->twitter)) { ?>
													<a target="_blank" href="<?= $rider->twitter ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a> <?php
												} if (isset($rider->facebook) AND !empty($rider->facebook)) { ?>
													<a target="_blank" href="<?= $rider->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a> <?php
												} if (isset($rider->instagram) AND !empty($rider->instagram)){ ?>
													<a target="_blank" href="<?= $rider->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a> <?php
												} if (isset($rider->strava) AND !empty($rider->strava)){ ?>
													<a target="_blank" href="<?= $rider->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a> <?php
												} ?>
											</div> <?php
											if ($rider->isFriend($connected_user)) { ?>
												<strong>Friends since :</strong><?= datetimeToDate($rider->friendsSince($connected_user->id)); 
											} ?>
										</div>
										<div class="rdr-sub"> <?php
											if (!empty($rider->place)) { ?>
												<div class="d-flex gap">
													<span class="iconify" data-icon="gis:poi-map" data-width="20"></span>
													<?= $rider->place; ?>
												</div> <?php
											} 
											if (!empty($rider->birthdate)) { ?>
												<strong>Age : </strong>
												<?= $rider->calculateAge(). ' years old';
											} ?>
										</div>
									</div>
								</div>
						
								<!-- Right container -->
								<div class="rdr-container-right"> <?php
									if (!empty($rider->level)) { ?>
										<div>
											<strong>Level : </strong>
											<span class="tag-<?= colorLevel($rider->level); ?>">
												<?= $rider->level; ?>
											</span>
										</div> <?php
									} 
									// If bike is set and bike type is filled
									if ($rider->getBikes()) { ?>
										<div class="mt-1 mb-1">
											<strong>Bikes : </strong> <?php
											foreach ($rider->getBikes() as $bike) {
												$bike = new Bike($bike['id']);
												if (!empty($bike->type)) { ?>
													<div class="tag"><?= $bike->type; ?></div> <?php
												} 
											} ?>
										</div> <?php
									} ?>
								</div>
						
						
								<!-- Buttons -->
								<div class="rdr-container-buttons"> <?php

									
									// If connected user don't already follow the rider
									if (!$connected_user->follows($rider)) { ?>
										<button id="rdr-follow-<?= $rider->id; // Generates dynamic id ?>" class="btn rdr-button success js-follow">
											<span class="iconify-inline" data-icon="mdi:eye-arrow-right-outline" style="color: white;" data-width="20" data-height="20"></span>
											Follow
										</button> <?php
									// If connected user already follows the rider
									} else { ?>
										<button id="rdr-unfollow-<?= $rider->id; // Generates dynamic id ?>" class="btn rdr-button danger js-unfollow">
											<span class="iconify-inline" data-icon="mdi:eye-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
											Unfollow
										</button> <?php
									}

									// If the rider is friend with connected user
									if ($rider->isFriend($connected_user)) { ?>
										<button id="rdr-remove-<?= $rider->id; // Generates dynamic id ?>" data-login="<?= $rider->login; ?>" class="btn rdr-button danger js-remove">
											<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
											Remove
										</button> <?php
									// If the rider has sent a request to connected user
									} else if (in_array($rider->id, $connected_user->getRequesters())) { ?>
										<button id="rdr-accept-<?= $rider->id; // Generates dynamic id ?>" data-login="<?= $rider->login; ?>" class="btn rdr-button success js-accept">
											<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>
											Accept
										</button>
										<button id="rdr-dismiss-<?= $rider->id; // Generates dynamic id ?>" data-login="<?= $rider->login; ?>" class="btn rdr-button danger js-dismiss">
											<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
											Dismiss
										</button> <?php
									// If the rider is not friend with connected user (and is not connected user himself)
									} else if ($rider->id != $connected_user->id AND !$rider->isFriend($connected_user)) { ?>
										<button id="rdr-add-<?= $rider->id; // Generates dynamic id ?>" data-login="<?= $rider->login; ?>" class="btn rdr-button success js-add">
											<span class="iconify-inline" data-icon="eva:person-add-outline" style="color: white;" data-width="20" data-height="20"></span>
											Add
										</button> <?php
									} ?>
									<a href="/riders/profile.php?id=<?= $rider->id ?>">
										<button class="btn rdr-button">
											Check
										</button>
									</a>
								</div>
							
							</div>
						</div>
						
					<?php
				}
			} else {
				
				$error = 'There is no rider to display.';
				
				if (isset($error)) {
					echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$error. '</p></div>'; 
				}
			}
			?>
			
		</div>
	</div>
	
</body>
</html>


<script src="/includes/riders/friends/friends.js"></script>