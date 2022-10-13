<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />

<?php 
session_start();
include '../includes/head.php';
include '../actions/users/securityAction.php';
require '../actions/rides/new/createAction.php';


// Clear session form data if ride already posted
if (isset($_SESSION['forms']['created'])) {
    $_SESSION['forms'][1] = array();
    $_SESSION['forms'][2] = array();
    $_SESSION['course'] = array();
}

// Prepare multipage system

// Detects if the url contains a variable
$url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

// If no parameter in the query string, reset all forms info
if (strpos($url,'?') == false) unset($_SESSION['forms']);

// Adapts index name to the current page
define('CFG_FORM_ACTION', basename(__FILE__));
 
// Gets current page number from the URL
if (empty($_GET['stage']) or !is_numeric($_GET['stage'])) define('CFG_STAGE_ID', 1);
else define('CFG_STAGE_ID', intval($_GET['stage']));
 
// Sets the session variable with an array that will contain all form infos
if (empty($_SESSION['forms'])) $_SESSION['forms'] = array();

include '../includes/navbar.php';
include '../actions/rides/new/dataProcessAction.php'; ?>
</html>