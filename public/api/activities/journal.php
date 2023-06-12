<?php

require '../../../includes/api-head.php';

if (isset($_GET)) {

    if ($_GET['task'] == 'activity_data') {

        $user = new User($_GET['user_id']);
        $year = intval($_GET['year']);
        $month = intval($_GET['month']);

        $activities = $user->getActivitiesByMonth($year, $month);

        $activities = array_map(function ($activity) {
            $activity->featured_photo = $activity->getFeaturedImage();
            return $activity;
        }, $activities);

        echo json_encode($activities);

    }

    if ($_GET['task'] == 'user_inscription_date') {

        $user = new User($_GET['user_id']);

        echo json_encode($user->inscription_date);

    }

}