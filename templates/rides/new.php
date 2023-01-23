<?php

include '../actions/users/initSessionAction.php';
require '../actions/rides/new/createAction.php';
include '../includes/head.php';

// Clear session form data if ride already posted
if (isset($_SESSION['forms']['created'])) {
    $_SESSION['forms'][1] = array();
    $_SESSION['forms'][2] = array();
    $_SESSION['course'] = array();
}

// Prepare multipage system

// Get stage number from URL slug
$slug = basename($_SERVER['REQUEST_URI']);
if (empty($slug) or !is_numeric($slug)) {
    unset($_SESSION['forms']); // If no slug in the query string, reset all forms info
    define('CFG_STAGE_ID', 1);
} else define('CFG_STAGE_ID', intval($slug));

// Get base URI
$base_uri = '/ride/new/';
 
// Sets the session variable with an array that will contain all form infos
if (empty($_SESSION['forms'])) $_SESSION['forms'] = array(); ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" /> <?php

include '../includes/navbar.php'; ?>

<div class="main"> <?php
    include '../actions/rides/new/dataProcessAction.php'; ?>
</div>

</html>