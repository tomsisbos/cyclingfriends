<?php

if (isset($_POST['twitter_disconnect'])) {
    $twitter = $connected_user->getTwitter();
    $twitter->disconnect();
}