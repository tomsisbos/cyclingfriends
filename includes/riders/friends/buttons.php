<div class="rdr-container-buttons"> <?php

	// If connected user don't already follow the rider
	if (!$connected_user->follows($user)) { ?>
		<button data-action="follow" data-id="<?= $user->id ?>" class="btn rdr-button success js-follower">
			<span class="iconify-inline" data-icon="mdi:eye-arrow-right-outline" style="color: white;" data-width="20" data-height="20"></span>
			Follow
		</button> <?php
	// If connected user already follows the rider
	} else { ?>
		<button data-action="unfollow" data-id="<?= $user->id ?>" class="btn rdr-button danger js-follower">
			<span class="iconify-inline" data-icon="mdi:eye-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
			Unfollow
		</button> <?php
	}

	// If the rider is friend with connected user
	if ($user->isFriend($connected_user)) { ?>
		<button data-action="remove" data-id="<?= $user->id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button danger js-friend">
			<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
			Remove friend
		</button> <?php
	// If the rider has sent a request to connected user
	} else if (in_array($user->id, $connected_user->getRequesters())){ ?>
		<button data-action="accept" data-id="<?= $user->id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button success js-friend">
			<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>
			Accept
		</button>
		<button data-action="dismiss" data-id="<?= $user->id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button danger js-friend">
			<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
			Dismiss
		</button> <?php
	// If connected user has sent a request to the rider
	} else if (in_array($connected_user->id, $user->getRequesters())){ ?>
		<button data-id="<?= $user->id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button no-click">
			Request sent...
		</button> <?php
	// If the rider is not friend with connected user (and is not connected user himself)
	} else if ($_SESSION['id'] != $user->id){ ?>
		<button data-action="add" data-id="<?= $user->id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button success js-friend">
			<span class="iconify-inline" data-icon="eva:person-add-outline" style="color: white;" data-width="20" data-height="20"></span>
			Become friend
		</button> <?php
	} ?>

</div>