<?php

$session_duration = '7 DAY';

// Initialize the PostgreSQL connection
$session_connection = pg_connect('host=' .getenv('DB_POSTGRESQL_HOST'). ' port=5432 dbname=' .getenv('DB_NAME'). ' user=' .getEnv('DB_USER'). ' password=' .getEnv('DB_PASSWORD'));

function on_session_start ($save_path, $session_name) {
    ///var_dump("on_session_start: " . $session_name . " " . session_id());
    return true;
}

function on_session_end () {
    // Note: Since we're not using PDO anymore, you may need to use the global $db here.
    global $session_connection;
    return true;
}

function on_session_read($key) {
    global $session_connection;
    try {
        $query = "SELECT data FROM sessions WHERE session_id = $1 AND expiry > NOW()";
        $result = pg_query_params($session_connection, $query, [$key]);

        if (pg_num_rows($result) > 0) {
            $row = pg_fetch_assoc($result);
            $dataResource = $row['data'];

            // Unescape the binary data to a string
            $unescapedData = pg_unescape_bytea($dataResource);

            return $unescapedData;
        }
    } catch (Exception $e) {
        error_log("on_session_read error: " . $e->getMessage());
    }

    return '';
}

function on_session_write ($key, $value) {
    global $session_connection;
    global $session_duration;
    try {
        $isSessionSet = pg_query_params($session_connection, "SELECT id FROM sessions WHERE session_id = $1", [$key]);

        $escaped_value = pg_escape_bytea($session_connection, $value);

        if (pg_num_rows($isSessionSet) > 0) {
            $updateSession = pg_query_params($session_connection, "UPDATE sessions SET last_updated = NOW(), expiry = NOW() + INTERVAL '{$session_duration}', data = $1 WHERE session_id = $2", [$escaped_value, $key]);
            $result = ($updateSession !== false);
        } else {
            $setSession = pg_query_params($session_connection, "INSERT INTO sessions (session_id, last_updated, expiry, data) VALUES ($1, NOW(), NOW() + INTERVAL '{$session_duration}', $2)", [$key, $escaped_value]);
            $result = ($setSession !== false);
        }
        return $result;
    } catch (Exception $e) {
        error_log("on_session_write error: " . $e->getMessage());
    }

    return true;
}

function on_session_destroy ($key) {
    global $session_connection;
    try {
        $destroySession = pg_query_params($session_connection, "DELETE FROM sessions WHERE session_id = $1", [$key]);
    } catch (Exception $e) {
        error_log("on_session_destroy error: " . $e->getMessage());
    }
    return true;
}

function on_session_gc ($max_lifetime) {
    global $session_connection;
    try {
        $cleanSession = pg_query($session_connection, "DELETE FROM sessions WHERE expiry <= NOW()");
    } catch (Exception $e) {
        error_log("on_session_gc error: " . $e->getMessage());
    }
}

// Set the save handlers
session_set_save_handler("on_session_start", "on_session_end", "on_session_read", "on_session_write", "on_session_destroy", "on_session_gc");