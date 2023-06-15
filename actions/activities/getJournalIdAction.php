<?php

require '../actions/databaseAction.php';

// Get id from URL
$last_parameter = basename($_SERVER['REQUEST_URI']);
if (is_numeric($last_parameter) || $last_parameter === 'journal') {

    if (is_numeric($last_parameter)) $user_id = $last_parameter;
    else {
        $url_fragments = explode('/', $_SERVER['REQUEST_URI']);
        $login = array_slice($url_fragments, -2)[0];
        $getUserIdFromLogin = $db->prepare("SELECT id FROM users WHERE login = ?");
        $getUserIdFromLogin->execute([$login]);
        $user_id = $getUserIdFromLogin->fetch(PDO::FETCH_COLUMN);
    }
}