<?php

$session_db = pg_pconnect("host=" .getEnv('DB_POSTGRESQL_HOST'). " port=5432 dbname=" .getenv('DB_NAME'). " user=" .getenv('DB_USER'). " password=" .getenv('DB_PASSWORD'));
$session_duration = '1 day';

function on_session_start ($save_path, $session_name) {
    error_log("on_session_start: " . $session_name . " ". session_id());
}

function on_session_end () {
    global $session_db;
    pg_close($session_db);
}

function on_session_read ($key) {
    global $session_db;
    #error_log("on_session_read: " . $key);
    $getData = $session_db->prepare("SELECT data FROM sessions WHERE id = ? AND CURRENT_TIMESTAMP < expiry");
    $getData->execute([$key]);

    if ($getData->rowCount() > 0) return $getData->fetchAll(PDO::FETCH_ASSOC);
    else return false;
}

function on_session_write ($key, $val) {
    global $session_db;
    global $session_duration;
    #error_log("on_session_write $key = $val");
    $val = pg_escape_string($val);
    $isSessionSet = $session_db->prepare("SELECT session_id FROM sessions WHERE id = ?");
    $isSessionSet->execute([$key]);
    if ($isSessionSet->rowCount() > 0) {
        $updateSession = $session_db->prepare("UPDATE sessions SET last_updated = CURRENT_TIMESTAMP, expiry = CURRENT_TIMESTAMP + interval {$session_duration}, data = ? WHERE id = ?");
        $updateSession->execute([$value, $key]);
    } else {
        $setSession = $session_db->prepare("INSERT INTO sessions VALUES (?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP + interval {$session_duration}, ?)");
        $updateSession->execute([$key, $value]);
    }
}

function on_session_destroy ($key) {
    global $session_db;
    $destroySession = $session_db->prepare("DELETE FROM sessions WHERE id = ?");
    $session_destroy->execute([$key]);
}

function on_session_gc ($max_lifetime) {
    global $session_db;
    $cleanSession = $session_db->prepare($session_db, "DELETE FROM sessions WHERE expiry > CURRENT_TIMESTAMP");
    $cleanSession->execute();
}


# Set the save handlers
session_set_save_handler("on_session_start", "on_session_end", "on_session_read", "on_session_write", "on_session_destroy", "on_session_gc");
