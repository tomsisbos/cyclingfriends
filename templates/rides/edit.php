<?php

include '../actions/users/initSession.php';
include '../includes/head.php';

// Prepare multipage system

// Get ride ID and stage number from URL slugs
$url_fragments = explode('/', $_SERVER['REQUEST_URI']);
if (!is_numeric(basename($_SERVER['REQUEST_URI']))) {
    $ride_slug = array_slice($url_fragments, -2)[0];
} else {
    $ride_slug = array_slice($url_fragments, -3)[0];
    $stage_slug = array_slice($url_fragments, -1)[0];
}

// Get base URI
$base_uri = '/ride/' . $ride_slug . '/edit/';

// Set session edit-form ride id ride id slug
if (isset($ride_slug)) $_SESSION['edit-forms']['ride-id'] = $ride_slug; 

/// Gets current page number from the URL
if (isset($stage_slug)) define('CFG_STAGE_ID', intval($stage_slug));
else define('CFG_STAGE_ID', 1);

include '../actions/rides/edit/getRide.php';
include '../actions/rides/edit/edit.php'; 
include '../includes/navbar.php';?>

<!DOCTYPE html>
<html lang="en">

    <link rel="stylesheet" href="/assets/css/ride.css" />

    <div class="main"> <?php
        include '../actions/rides/edit/dataProcess.php'; ?>
    </div>

</html>