<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {

        if (isset($_GET['prefecture'])) {
            $getSceneriesFromPrefecture = $db->prepare('SELECT id FROM sceneries WHERE prefecture = ? ORDER BY rating DESC');
            $getSceneriesFromPrefecture->execute([$_GET['prefecture']]);
            $scenery_ids = $getSceneriesFromPrefecture->fetchAll(PDO::FETCH_COLUMN);
            $sceneries = array_map(function ($id) {
                return new Scenery($id);
            }, $scenery_ids);
            echo json_encode($sceneries);
        }

    }

}