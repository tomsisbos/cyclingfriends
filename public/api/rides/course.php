<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

$json = file_get_contents('php://input'); // Get json file from course.js xhr request

$var = json_decode($json, true);

// Define form name
if (isset($var['edit'])) {
    $form_name = 'edit-course';
    unset($var['edit']);
} else $form_name = 'course';

// On updateSession call
if (isset($var['method'])) $_SESSION[$form_name]['method'] = $var['method'];
if (isset($var['data'])) {
    forEach ($var['data'] as $key => $data) {
        $_SESSION[$form_name][$key] = $data;
    }
    echo json_encode($_SESSION[$form_name]);
}
// On clearSession call
if (isset($var['clear'])) {
    if (isset($_SESSION[$form_name])) {
        foreach ($_SESSION[$form_name] as $key => $entry) {
            unset($_SESSION[$form_name][$key]);
        }
        $_SESSION[$form_name]['checkpoints'] = [];
        $_SESSION[$form_name]['meetingplace'] = [];
        $_SESSION[$form_name]['finishplace'] = [];
        echo json_encode($_SESSION[$form_name]);
    } else {
        $_SESSION[$form_name] = ['checkpoints', 'meetingplace', 'finishplace'];
        echo json_encode($_SESSION[$form_name]);
    }
}
// On editcaption call
if (isset($var['field'])) {
    $updateCaption = $db->prepare('UPDATE ride_checkpoints SET '. $var['field'] .' = ? WHERE ride_id = ? AND checkpoint_id = ? ');
    $updateCaption->execute(array(htmlspecialchars($var['value']), $var['ride_id'], $var['checkpoint_id']));
    echo json_encode($var);
}
