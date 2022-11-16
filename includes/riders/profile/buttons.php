<div class="td-row push gap-30"> <?php

	if ($_SESSION['id'] != $user->id) { ?>	
		<a href="#">
			<button id="sendMessageButton" class="btn button" name="send_message">Send message</button>
		</a>
		<div class="rdr-container-buttons"> <?php

			// If connected user don't already follow the rider
			if (!$connected_user->follows($user)) { ?>
				<button id="rdr-follow-<?= $user->id; // Generates dynamic id ?>" class="btn rdr-button success js-follow">
					<span class="iconify-inline" data-icon="mdi:eye-arrow-right-outline" style="color: white;" data-width="20" data-height="20"></span>
					Follow
				</button> <?php
			// If connected user already follows the rider
			} else { ?>
				<button id="rdr-unfollow-<?= $user->id; // Generates dynamic id ?>" class="btn rdr-button danger js-unfollow">
					<span class="iconify-inline" data-icon="mdi:eye-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
					Unfollow
				</button> <?php
			}

			// If the rider is friend with connected user
			if ($user->isFriend($connected_user)) { ?>
				<button id="rdr-remove-<?= $user->id; // Generates dynamic id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button danger js-remove">
					<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
					Remove
				</button> <?php
			// If the rider has sent a request to connected user
			} else if (in_array($user->id, $connected_user->getRequesters())){ ?>
				<button id="rdr-accept-<?= $user->id; // Generates dynamic id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button success js-accept">
					<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>
					Accept
				</button>
				<button id="rdr-dismiss-<?= $user->id; // Generates dynamic id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button danger js-dismiss">
					<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
					Dismiss
				</button> <?php
			// If the rider is not friend with connected user (and is not connected user himself)
			} else if ($_SESSION['id'] != $user->id){ ?>
				<button id="rdr-add-<?= $user->id; // Generates dynamic id ?>" data-login="<?= $user->login; ?>" class="btn rdr-button success js-add">
					<span class="iconify-inline" data-icon="eva:person-add-outline" style="color: white;" data-width="20" data-height="20"></span>
					Add
				</button> <?php
			}

			
			// Include send message button
			include '../includes/riders/profile/send-message.php'; ?>

		</div> <?php 

	} else { ?>
		<a href="/riders/profile/edit.php">
			<button class="button btn">
				Edit my profile
			</button>
		</a> <?php
	} ?>

</div>