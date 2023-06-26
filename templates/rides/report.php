<?php

include '../actions/users/initPublicSessionAction.php';
include '../actions/rides/rideAction.php';
include '../actions/rides/edit/adminPanelAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map.css" />
<link rel="stylesheet" href="/assets/css/youtube.css" />
<link rel="stylesheet" href="/assets/css/activity.css" />

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body> <?php

	// If set as private and connected user does not have admin rights on this ride, redirect to the dashboard
	if ($ride->privacy == 'private' AND (!isset($_SESSION['auth']) OR $ride->author_id != getConnectedUser()->id)) {
		header('Location: /');
	}
	
	// If set as Friends only and connected user is not on the friends list on the ride author, redirect to the dashboard
	if ($ride->privacy == 'friends_only' AND (!isset($_SESSION['auth']) OR (isset($_SESSION['auth']) && $ride->author_id != getConnectedUser()->id AND !$ride->getAuthor()->isFriend(getConnectedUser())))) {
		header('Location: /');
	}

	include '../includes/navbar.php'; ?>

	<div class="main container-shrink"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>
        
        <div class="with-background-img full-header fullwidth js-fade-on-scroll" data-parameter="activity-imgs" data-value="<?= $ride->getReport()->activity_id ?>" data-interval="6" data-overlay-color="#000"></div>

            <div class="full-header with-background-flash" style="position: relative">
                <div class="header-block">
                    <div class="header-row mb-2">
                        <div class="rd-status">
                            <p class="tag-dark text-light">ライドレポート</p> <?php
                            // Only add substatus if there is one
                            if ($ride->privacy == 'friends_only') { ?>
                                <p style="background-color: #ff5555" class="tag-light text-light"><?= $ride->getAuthor()->login; ?>の友達に限定</p> <?php
                            } ?>
                        </div>
                    </div>
                    <div class="header-row">
                        <h2><?= $ride->name ?></h2>
                    </div>
                    <div class="header-row">
                        <a href="/rider/<?= $ride->author_id ?>"><?php $ride->getAuthor()->getPropicElement(30, 30, 30); ?></a>
                        <p>by <strong><?= $ride->getAuthor()->login ?></strong></p>
                    </div>
                    <div class="header-row mt-2"> <?php
                        // Include admin buttons if the user has admin rights on this ride
                        if (isset($_SESSION['auth']) && $ride->author_id == getConnectedUser()->id) include '../includes/rides/admin-buttons.php';
                        if (isset($ride->getReport()->photoalbum_url)) echo '<a href="' .$ride->getReport()->photoalbum_url. '" target="_blank"><button class="mp-button success">フォトアルバム</div></a>' ?>
                    </div>
                </div>
            </div>
        </div> <?php

        // Participants
        include '../includes/rides/participants.php';

        // Video if set
        if (isset($ride->getReport()->video_url)) { ?>
            <div class="container px-0 text-center">
                <?= $ride->getReport()->getVideoIframe() ?>
            </div> <?php
        }
    
        // Report timeline and map
        $activity = new Activity($ride->getReport()->activity_id); ?>
        
        <div class="container pg-ac-summary-container"> <?php
            include '../includes/activities/timeline.php' ?>
        </div>

        <div class="container p-0"> <?php
            include '../includes/activities/map.php' ?>
        </div>

	</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>
<script src="/assets/js/fade-on-scroll.js"></script>
<script type="module" src="/scripts/activities/activity.js"></script>