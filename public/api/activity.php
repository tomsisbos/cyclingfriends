<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {

        if (isset($_GET['load'])) {
            $activity = new Activity($_GET['load'], false);
            $activity->checkpoints = $activity->getCheckpoints();
            $activity->photos = $activity->getPhotos();
            $activity->coordinates = $activity->getLinestring();
            $activity->time = $activity->getTime();
            echo json_encode($activity);
        }

        if (isset($_GET['delete'])) {
            $activity = new Activity($_GET['delete']);
            if ($connected_user->id == $activity->user_id) $activity->delete();
            else "このアクティビティを削除する権限がありません。";
            echo json_encode($connected_user->login);
        }

    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);