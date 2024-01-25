<?php

require '../../includes/api-head.php';

if (isset($_GET['route'])) {
    $route = new Route($_GET['route']);
    $close_sceneries = $route->getLinestring()->getCloseSceneries();
    echo json_encode($close_sceneries);
}

if (isset($_GET['prefecture'])) {
    $getSceneriesFromPrefecture = $db->prepare('SELECT id FROM sceneries WHERE prefecture = ? ORDER BY publication_date DESC');
    $getSceneriesFromPrefecture->execute([$_GET['prefecture']]);
    $scenery_ids = $getSceneriesFromPrefecture->fetchAll(PDO::FETCH_COLUMN);
    $sceneries = array_map(function ($id) {
        return new Scenery($id);
    }, $scenery_ids);
    echo json_encode($sceneries);
}