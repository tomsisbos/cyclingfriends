<div class="rdr-container-buttons"> <?php

	// If rider and connected user are not the same
	if ($connected_user->id !== $rider->id) {
		// If connected user don't already follow the rider
		if (!$connected_user->follows($rider)) { ?>
			<button data-action="follow" data-id="<?= $rider->id ?>" class="rdr-button success js-follower">
				<span class="iconify-inline" data-icon="mdi:eye-arrow-right-outline" style="color: white;" data-width="20" data-height="20"></span>
				Follow
			</button> <?php
		// If connected user already follows the rider
		} else { ?>
			<button data-action="unfollow" data-id="<?= $rider->id ?>" class="rdr-button danger js-follower">
				<span class="iconify-inline" data-icon="mdi:eye-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
				Unfollow
			</button> <?php
		}
	}

	// If the rider is friend with connected user
	if ($rider->isFriend($connected_user)) { ?>
		<button data-action="remove" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button danger js-friend">
			<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
			Remove friend
		</button> <?php
	// If the rider has sent a request to connected user
	} else if (in_array($rider->id, $connected_user->getRequesters())){ ?>
		<button data-action="accept" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button success js-friend">
			<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>
			Accept
		</button>
		<button data-action="dismiss" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button danger js-friend">
			<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
			Dismiss
		</button> <?php
	// If connected user has sent a request to the rider
	} else if (in_array($connected_user->id, $rider->getRequesters())){ ?>
		<button data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button no-click">
			Request sent...
		</button> <?php
	// If the rider is not friend with connected user (and is not connected user himself)
	} else if ($_SESSION['id'] != $rider->id){ ?>
		<button data-action="add" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button success js-friend">
			<span class="iconify-inline" data-icon="eva:person-add-outline" style="color: white;" data-width="20" data-height="20"></span>
			Become friend
		</button> <?php
	} ?>

</div>