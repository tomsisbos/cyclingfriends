<!-- Friends list -->
<div class="container container-thin d-flex gap-20 nav bg-friend">
		<h2 class="title-with-subtitle">Friends :</h2>
		<div class="d-flex" id="friendsList">
			<?php $friends = $user->getFriends();
			if (!empty($friends)) {
                if (count($friends) >= 15) $limit = 15;
                else $limit = count($friends);
				for ($i = 0; $i < $limit; $i++) {
					$friend = new User ($friends[$i]); ?>
					<div class="superpose"> <?php
						$friend->displayPropic(40, 40, 40); ?>
					</div> <?php
					if ($i == 14) echo '<span style="width: 100%; margin-left: 30px; align-self: center;">...and others</span>';
				}
			} else echo 'No friends to display'; ?>
		</div>

		<!-- Friends lighbox window -->
		<div id="friendsWindow" class="modal modal-small" style="display: none;">
			<span class="close cursor" onclick="closeFriendsWindow()">&times;</span>
			<div class="modal-block modal-block-thin p-2">
				<div class="container bg-friend">
					<h2 class=""><?= $user->login. "'s friends"; ?></h2>
				</div>
				<div class="container overflow-400">
					<div class="tr-row justify th-row bg-grey mb-2">
						<div class="td-row element-30">
						</div>
						<div class="td-row element-30">
							Login
						</div>
						<div class="td-row element-40">
							Place
						</div>
					</div>
					<?php
					if (!empty($friends)) {
						foreach ($friends as $friend) {
							$friend = new User ($friend); ?>
							<div class="tr-row justify">
								<div class="td-row element-30">
									<a style="text-decoration: none;" href="/riders/profile.php?id=<?= $friend->id ?>"><?php $friend->displayPropic(60, 60, 60); ?></a>
								</div>
								<div class="td-row element-30">
									<?= $friend->login ?>
								</div>
								<div class="td-row element-40">
									<?= $friend->place ?>
								</div>
							</div> <?php
						}
					} else echo 'No friends to display'; ?>
				</div>
			</div>
		</div>
	</div>