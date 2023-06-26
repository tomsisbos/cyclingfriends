	<?php
		
	$requesters = getConnectedUser()->getRequesters();
		
	if ($requesters) { ?>
		
		<div class="container">
			<h3>申請リスト</h3>
		</div>
			
		<div class="container bg-white"> <?php
		
			forEach ($requesters as $requester) {
				
				$rider = new User ($requester) ?>
	
				<div class="rdr-card bg-rider">
					<div class="rdr-card-inner">
			
						<!-- Profile picture -->
						<div class="rdr-propic">
							<a href="/rider/<?= $rider->id ?>"><?php $rider->getPropicElement(80, 80, 80); ?></a>
						</div>
				
						<!-- Left container -->
						<div class="rdr-container-left">
							<a class="normal" href="/rider/<?= $rider->id ?>">
								<div class="rdr-login-section"> <?php 
									if (!empty($rider->gender)) { ?>
										<div class="rdr-gender">	<?php
											echo getGenderAsIcon($rider->gender); ?>
										</div> <?php
										} ?>
									<div class="rdr-login"><?= $rider->login; ?></div>
									<div class="rdr-name"><?php
										if (!empty($rider->last_name) AND !empty($rider->first_name) AND $rider->isRealNamePublic()) echo '- (' .strtoupper($rider->last_name);
										if (!empty($rider->first_name AND $rider->isRealNamePublic())) echo ' ' .ucfirst($rider->first_name. ')'); ?>
									</div>
								</div>
							</a>
							<div class="rdr-maininfos-section">
								<div class="rdr-sub">
									<div class="d-flex gap"> <?php
									// Only display social links if filled
									if ($user->getTwitter()->isUserConnected()) {
										$twitter = $user->getTwitter(); ?>
										<a target="_blank" href="<?= $twitter->url ?>"><span class="social iconify twitter" data-icon="ant-design:twitter-circle-filled" data-width="20"></span></a><?php
									} if (isset($rider->facebook) AND !empty($rider->facebook)) { ?>
										<a target="_blank" href="<?= $rider->facebook ?>"><span class="social iconify facebook" data-icon="akar-icons:facebook-fill" data-width="20"></span></a><?php
									} if (isset($rider->instagram) AND !empty($rider->instagram)) { ?>
										<a target="_blank" href="<?= $rider->instagram ?>"><span class="social iconify instagram" data-icon="ant-design:instagram-filled" data-width="20"></span></a><?php
									} if (isset($rider->strava) AND !empty($rider->strava)) { ?>
										<a target="_blank" href="<?= $rider->strava ?>"><span class="social iconify strava" data-icon="bi:strava" data-width="20"></span></a><?php
									} ?>
									</div>
								</div>
								<div class="rdr-sub"> <?php
									if (!empty($rider->place)) { ?>
										<div class="d-flex gap">
											<span class="iconify" data-icon="gis:poi-map" data-width="20"></span>
											<?= $rider->place; ?>
										</div> <?php
									} 
									if (!empty($rider->birthdate) && $rider->isAgePublic()) { ?>
										<div>
											<strong>年齢 : </strong>
											<?= $rider->calculateAge(). ' 才'; ?>
										</div> <?php
									} ?>
								</div>
							</div>
						</div>
				
						<!-- Right container -->
						<div class="rdr-container-right"> <?php
							if (!empty($rider->level)) { ?>
								<strong>レベル : </strong>
								<span class="tag-<?= $rider->colorLevel($rider->level); ?>">
									<?= $rider->getLevelString(); ?>
								</span> <?php
							} 
							// If bike is set and bike type is filled
							if ($rider->getBikes()) { ?>
								<div class="mt-1 mb-1">
									<strong>バイク : </strong> <?php
									foreach ($rider->getBikes() as $bike_id) {
										$bike = new Bike($bike_id);
										if (!empty($bike->type)) { ?>
											<div class="tag">
												<?= $bike->type; ?>
											</div>
										<?php } 
									} ?>
								</div> <?php
							} ?>
						</div>
				
						<!-- Buttons -->
						<div class="rdr-container-buttons">
							<button data-action="accept" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button success js-friend">
								<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>
								友達申請を承認する
							</button>
							<button data-action="dismiss" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button danger js-friend">
								<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
								友達申請を却下する
							</button>
						</div>
					
					</div>
				</div>
				
			<?php
			} ?>
		</div> <?php
	} ?>
	