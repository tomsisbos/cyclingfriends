<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';

// Prepare multipage system

// Adapts index name to the current page
define('CFG_FORM_ACTION', basename(__FILE__));

// Set session edit-form ride id to id parameter
if (isset($_GET['id'])) $_SESSION['edit-forms']['ride-id'] = $_GET['id']; 

/// Gets current page number from the URL
if (empty($_GET['stage']) or !is_numeric($_GET['stage'])) define('CFG_STAGE_ID', 1);
else define('CFG_STAGE_ID', intval($_GET['stage']));

include '../actions/rides/edit/getRideAction.php';
require '../actions/rides/edit/editAction.php'; 
include '../includes/navbar.php'; ?>
<div class="main"> <?php
    include '../actions/rides/edit/dataProcessAction.php'; ?>
</div>
</html>