	<div class="container end bg-white"> <?php 
		
		if($getFriendsData->rowCount() > 0){

			while($friend = $getFriendsData->fetch()){
				
				$rider = new User ($friend['id']) ?>
			
				<div class="rdr-card bg-friend">
					<div class="rdr-card-inner">
			
						<!-- Profile picture -->
						<div class="rdr-propic">
							<a href="/riders/profile.php?id=<?= $rider->id ?>"><?php $rider->displayPropic(80, 80, 80); ?></a>
						</div>
				
						<!-- Left container -->
						<div class="rdr-container-left">
							<a class="normal" href="/riders/profile.php?id=<?= $rider->id ?>">
								<div class="rdr-login-section"> <?php 
									if(!empty($rider->gender)){ ?>
										<div class="rdr-gender">	<?php
											echo getGenderAsIcon($rider->gender); ?>
										</div> <?php
										} ?>
									<div class="rdr-login js-login"><?= $rider->login; ?></div>
									<div class="rdr-name"><?php
										if(!empty($rider->last_name) AND !empty($rider->first_name)){
											echo '- (' .strtoupper($rider->last_name);
										}
										if(!empty($rider->first_name)){
											echo ' ' .ucfirst($rider->first_name. ')');
										} ?>
									</div>
								</div>
							</a>
							<div class="rdr-maininfos-section">
								<div class="rdr-sub">
									<div class="d-flex gap">
									<!-- Only display social links if filled -->
									<?php if(isset($rider->twitter) AND !empty($rider->twitter)){ ?>
										<a target="_blank" href="<?= $rider->twitter ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a>
									<?php }if(isset($rider->facebook) AND !empty($rider->facebook)){ ?>
										<a target="_blank" href="<?= $rider->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a>
									<?php }if(isset($rider->instagram) AND !empty($rider->instagram)){ ?>
										<a target="_blank" href="<?= $rider->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a>
									<?php }if(isset($rider->strava) AND !empty($rider->strava)){ ?>
										<a target="_blank" href="<?= $rider->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a>
									<?php } ?>
									</div>
									<div>
									<strong>Friends since :</strong><?= datetimeToDate($rider->friendsSince($connected_user->id));
									?>
									</div>
								</div>
								<div class="rdr-sub">
									<?php if(!empty($rider->place)){ ?>
										<div class="d-flex gap">
											<span class="iconify" data-icon="gis:poi-map" data-width="20"></span>
											<?= $rider->place; ?>
										</div>
									<?php } 
									if(!empty($rider->birthdate)){ ?>
									<div>
										<strong>Age : </strong>
										<?= $rider->calculateAge(). ' years old'; ?>
									</div>
									<?php } ?>
								</div>
							</div>
						</div>
				
						<!-- Right container -->
						<div class="rdr-container-right">
							<?php if (!empty($rider->level)) { ?>
								<div>
									<strong>Level : </strong>
									<span class="tag-<?= colorLevel($rider->level); ?>">
										<?= $rider->level; ?>
									</span>
								</div>
							<?php } 
							// If bike is set and bike type is filled
							if ($rider->getBikes()) { ?>
								<div class="mt-1 mb-1">
									<strong>Bikes : </strong> <?php
									$types = []; 
									foreach ($rider->getBikes() as $bike) {
										$bike = new Bike ($bike['id']); 
										// Only display bike type once, even if more than one bike of this type registered
										if (!empty($bike->type AND !in_array($bike->type, $types))) { ?>
											<div class="tag"><?= $bike->type; ?></div> <?php
											$types[] = $bike->type;
										}
									} ?>
								</div> <?php
							} ?>
						</div>
				
				
						<!-- Buttons -->
						<div class="rdr-container-buttons">
							<button id="rdr-remove-<?= $rider->id; // Generates dynamic id ?>" data-login="<?= $rider->login; ?>" class="btn rdr-button danger js-remove">
							<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
								Remove
							</button>
						</div>
					
					</div>
				</div>
				
			<?php
			}
		}else{
			
			$suberrormessage = 'No friend has been found. You can add friends by clicking on the "Become friends" button on user\'s profile page.'; 
			
			if(isset($suberrormessage)){
				echo '<div class="error-block fullwidth text-center"><p class="error-message">' .$suberrormessage. '</p></div>';
			}
		}
		?>
		
	</div>