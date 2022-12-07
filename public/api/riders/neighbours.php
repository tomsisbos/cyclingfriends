<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-neighbours'])) {
        include '../../../actions/riders/displayNeighboursAction.php';
        foreach ($riders as $rider) $rider->propic = $rider->getPropicSrc();
        echo json_encode($riders);
    }

    if (isset($_GET['get-rider-data'])) {
        $rider = new User ($_GET['get-rider-data']);
        $rider->activitiesNumber = $rider->getActivitiesNumber();
        $rider->clearedSegmentsNumber = $rider->countClearedSegments();
        $rider->clearedMkpointsNumber = $rider->countClearedMkpoints();
        $rider->lastActivityPhotos = $rider->getLastActivityPhotos(3);
        $rider->propic = $rider->getPropicSrc();
        echo json_encode($rider);
    }

}