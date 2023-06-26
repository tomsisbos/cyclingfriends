<?php

if (isset($_POST['twitter_disconnect'])) {
    $twitter = getConnectedUser()->getTwitter();
    $twitter->disconnect();
}