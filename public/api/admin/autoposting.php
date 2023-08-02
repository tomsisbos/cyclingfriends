<?php

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET) && !empty($_GET)) {

        if ($_GET['type'] == 'schedule') {
            $getPostingSchedule = $db->prepare("SELECT id FROM autoposting WHERE entry_type = ? AND api = 'twitter' ORDER BY id ASC");
            $getPostingSchedule->execute([$_GET['entry_type']]);
            $entry_ids = $getPostingSchedule->fetchAll(PDO::FETCH_COLUMN);
            $entries = array_map(function ($id) {
                return (new AutopostingEntry())->populate($id);
            }, $entry_ids);
            echo json_encode($entries);
        }

        if ($_GET['type'] == 'remove') {
            $id = $_GET['id'];
            $entry = new AutopostingEntry();
            $entry->populate($id);
            $entry->remove();
            echo json_encode(true);
        }
    }
}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$var = json_decode($json, true);

if (is_array($var)) {
    $entry_id = $var['entry_id'];
    $entry_type = $var['entry_type'];
    $entry = new AutopostingEntry($entry_type, $entry_id, "twitter");
    $entry->generate();
    echo json_encode($entry);
}