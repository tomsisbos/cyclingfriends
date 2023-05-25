<?php

include '../actions/twitter/authentificationAction.php';

// Display social buttons if user is connected
if (isset($_SESSION['auth'])) { ?>

    <div class="social-panel"> <?php

        // Twitter
        $twitter = $connected_user->getTwitter();
        if (!$twitter->isUserConnected()) {
            $_SESSION['redirect_uri'] = $_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            echo '<a href="' .$twitter_auth_url. '">';
        } ?>
        <div class="icon" id="buttonTwitter" <?php if ($twitter->isUserConnected()) echo 'data-username="' .$twitter->username. '" data-name="' .$twitter->name. '" data-profile-image="' .$twitter->profile_image. '"' ?>>
            <span class="iconify" data-icon="fa:twitter-square" data-width="28" data-height="28"></span>
        </div> <?php
        if (!$twitter->isUserConnected()) echo '</a>' ?>

    </div> <?php
} ?>