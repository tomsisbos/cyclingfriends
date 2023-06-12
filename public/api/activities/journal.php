<?php

require '../../../includes/api-head.php';

if (isset($_GET)) {

    if (isset($_GET['user_id'])) {

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

}