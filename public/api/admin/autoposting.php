<?php

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET) && !empty($_GET)) {

        if ($_GET['type'] == 'schedule') {
            $getPostingSchedule = $db->prepare('SELECT id FROM autoposting WHERE entry_type = ? ORDER BY datetime ASC');
            $getPostingSchedule->execute([$_GET['entry_type']]);
            $entry_ids = $getPostingSchedule->fetchAll(PDO::FETCH_COLUMN);
            $entries = array_map(function ($id) {
                return new AutopostingEntry($id);
            }, $entry_ids);
            echo json_encode($entries);
        }
    }
}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$var = json_decode($json, true);

if (is_array($var)) {
    $entry_id = $var['entry_id'];
    $entry_type = $var['entry_type'];
    $id = getNextAutoIncrement('autoposting');
    $insertAutopostingEntry = $db->prepare('INSERT INTO autoposting (entry_id, entry_type, datetime) VALUES (?, ?, ?)');
    $insertAutopostingEntry->execute([$entry_id, $entry_type, ]);
    echo json_encode(new AutopostingEntry($id));
}