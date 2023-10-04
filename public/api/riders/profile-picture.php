<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// Return info about connected user rights
if (isset($_GET['user'])) {
    $user = new User($_GET['user']);
    echo json_encode($user->getPropicUrl());
}