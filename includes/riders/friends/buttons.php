<div class="rdr-container-buttons"> <?php

	// If user is connected
	if (isSessionActive()) {

		// If rider and connected user are not the same
		if (getConnectedUser()->id !== $rider->id) {
			// If connected user don't already follow the rider
			if (!getConnectedUser()->follows($rider)) { ?>
				<button data-action="follow" data-id="<?= $rider->id ?>" class="rdr-button success js-follower">
					<span class="iconify-inline" data-icon="mdi:eye-arrow-right-outline" style="color: white;" data-width="20" data-height="20"></span>
					フォローする
				</button> <?php
			// If connected user already follows the rider
			} else { ?>
				<button data-action="unfollow" data-id="<?= $rider->id ?>" class="rdr-button danger js-follower">
					<span class="iconify-inline" data-icon="mdi:eye-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
					フォローを辞める
				</button> <?php
			}
		}

		// If the rider is friend with connected user
		if ($rider->isFriend(getConnectedUser())) { ?>
			<button data-action="remove" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button danger js-friend">
				<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
				友達を辞める
			</button> <?php
		// If the rider has sent a request to connected user
		} else if (in_array($rider->id, getConnectedUser()->getRequesters())){ ?>
			<button data-action="accept" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button success js-friend">
				<span class="iconify-inline" data-icon="eva:person-done-outline" style="color: white;" data-width="20" data-height="20"></span>
				友達申請を承認する
			</button>
			<button data-action="dismiss" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button danger js-friend">
				<span class="iconify-inline" data-icon="eva:person-remove-outline" style="color: white;" data-width="20" data-height="20"></span>
				友達申請を却下する
			</button> <?php
		// If connected user has sent a request to the rider
		} else if (in_array(getConnectedUser()->id, $rider->getRequesters())){ ?>
			<button data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button no-click">
				友達申請中...
			</button> <?php
		// If the rider is not friend with connected user (and is not connected user himself)
		} else if ($_SESSION['id'] != $rider->id){ ?>
			<button data-action="add" data-id="<?= $rider->id ?>" data-login="<?= $rider->login; ?>" class="rdr-button success js-friend">
				<span class="iconify-inline" data-icon="eva:person-add-outline" style="color: white;" data-width="20" data-height="20"></span>
				友達申請を送る
			</button> <?php
		}
} ?>

</div>