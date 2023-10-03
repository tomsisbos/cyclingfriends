<?php

require '../actions/database.php';

// Get token
$last_parameter = basename($_SERVER['REQUEST_URI']);
if (is_numeric($last_parameter)) $token = $last_parameter;
else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/');

// Check if token corresponds to a privatebeta member entry (if user has already registered)
$checkIfTokenIsValid = $db->prepare("SELECT id FROM privatebeta_members WHERE token = ?");
$checkIfTokenIsValid->execute([$token]);
if ($checkIfTokenIsValid->rowCount() > 0) {

    // Check if an user id is registered in privatebeta member table (if user has not already created an account)
    $checkIfUserExists = $db->prepare("SELECT user_id FROM privatebeta_members WHERE token = ? AND user_id IS NOT NULL");
    $checkIfUserExists->execute([$token]);
    if ($checkIfUserExists->rowCount() == 0) {

        $member = new PrivateBetaMember($token);

    } else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/signin');

} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/privatebeta/registration/' .$token);