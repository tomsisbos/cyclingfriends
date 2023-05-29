<!-- Friends list -->
<h3 class="title-with-subtitle">Friends :</h3>
<div class="d-flex" id="friendsList">
	<?php $friends = $user->getFriends();
	$limit = 10;
	if (!empty($friends)) {
		if (count($friends) < $limit) $number_to_display = count($friends);
		else $number_to_display = $limit;
		for ($i = 0; $i < $number_to_display; $i++) {
			$friend = new User ($friends[$i]); ?>
			<div class="superpose"> <?php
				$friend->getPropicElement(40, 40, 40); ?>
			</div> <?php
			if ($i == $limit - 1) echo '<span style="width: 100%; margin-left: 30px; align-self: center;">...他'. (count($friends) - $limit) .'名</span>';
		}
	} else echo '表示するデータはありません。'; ?>
</div>

<!-- Friends lighbox window -->
<div id="friendsWindow" class="modal modal-small" style="display: none;">
	<div class="modal-block p-2">
		<span class="close cursor" onclick="closeFriendsWindow()">&times;</span>
		<div class="container bg-friend">
			<h3 class=""><?= $user->login. "'s friends"; ?></h3>
		</div>
		<div class="small-rdr-cards-container container"> <?php
			if (!empty($friends)) {
				foreach ($friends as $friend) {
					$rider = new User ($friend);
					include '../includes/riders/small-card.php';
				}
			} else echo '表示するデータはありません。'; ?>
		</div>
	</div>
</div>