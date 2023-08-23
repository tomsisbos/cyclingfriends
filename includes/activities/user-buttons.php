<?php

include '../actions/twitter/authentification.php';

// Display social buttons if user is connected
if (isSessionActive()) { 

    // Twitter
    $twitter = getConnectedUser()->getTwitter();
    if (!$twitter->isUserConnected()) {
        $_SESSION['redirect_uri'] = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        echo '<a href="' .$twitter_auth_url. '">';
    } ?>
    <div class="mp-button twitter-button" id="buttonTwitter" <?php if ($twitter->isUserConnected()) echo 'data-username="' .$twitter->username. '" data-name="' .$twitter->name. '" data-profile-image="' .$twitter->profile_image. '"' ?>>
        <span class="iconify" data-icon="mdi:twitter" data-width="20" data-height="20"></span>
    </div> <?php
    if (!$twitter->isUserConnected()) echo '</a>';

} ?>