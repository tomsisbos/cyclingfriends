<?php

require_once '../actions/users/initPublicSession.php';
require_once '../actions/rides/ride.php';
require_once '../actions/rides/edit/adminPanel.php';
require_once '../includes/head.php';
require_once '../includes/head-map.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map.css" />
<link rel="stylesheet" href="/assets/css/youtube.css" />
<link rel="stylesheet" href="/assets/css/activity.css" />
<link rel="stylesheet" href="/assets/css/twitter.css" />

<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body> <?php

	// If set as private and connected user does not have admin rights on this ride, redirect to the dashboard
	if ($ride->privacy == 'private' AND (!isSessionActive() OR $ride->author_id != getConnectedUser()->id)) {
		header('Location: /');
	}
	
	// If set as Friends only and connected user is not on the friends list on the ride author, redirect to the dashboard
	if ($ride->privacy == 'friends_only' AND (!isSessionActive() OR (isSessionActive() && $ride->author_id != getConnectedUser()->id AND !$ride->getAuthor()->isFriend(getConnectedUser())))) {
		header('Location: /');
	}

	include '../includes/navbar.php'; ?>

	<div class="main container-shrink"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; 
    
        // Report timeline and map
        $activity = new Activity($ride->getReport()->activity_id); ?>
		
		<div id="activity"

            data-activity="<?= $activity->id ?>"
            data-title="<?= $activity->title ?>" <?php
            if (isset($activity->getFeaturedImage()->url)) echo 'data-featured-image-url="' .$activity->getFeaturedImage()->url. '"';

            // Twitter data
            if (getConnectedUser()) {
                $twitter = getConnectedUser()->getTwitter();
                if (!$twitter->isUserConnected()) {
                    $_SESSION['redirect_uri'] = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                    echo 'data-twitter-auth-url="' .$twitter_auth_url. '"';
                }
                if ($twitter->isUserConnected()) echo 'data-twitter-username="' .$twitter->username. '" data-twitter-name="' .$twitter->name. '" data-twitter-profile-image="' .$twitter->profile_image. '"';
            }
        
            // Video if set
            if (isset($ride->getReport()->video_url)) echo 'data-youtube-element="' .$ride->getReport()->getVideoId(). '"';
        
            // Photo album if set
            if (isset($ride->getReport()->photoalbum_url)) echo 'data-photo-album-url="' .$ride->getReport()->photoalbum_url. '"'; ?>
        >
        </div>

	</div>

</body>
</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

<!-- Load React component -->
<script type="module" src="/react/runtime.js"></script>
<script type="module" src="/react/activity.js"></script>