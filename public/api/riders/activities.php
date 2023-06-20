<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['user_id'])) {
        $user = new User($_GET['user_id']);
        $activities = $user->getActivities(0, 20, 'datetime', 'public');
        echo json_encode($activities);
    }

}