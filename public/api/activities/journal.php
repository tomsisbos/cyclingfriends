<?php

require '../../../includes/api-head.php';

if (isset($_GET)) {

    if ($_GET['task'] == 'activity_data') {

        $user = new User($_GET['user_id']);
        $year = intval($_GET['year']);
        $month = intval($_GET['month']);

        $getActivitiesByDate = $db->prepare("SELECT a.id, a.title, a.datetime, r.distance, a.duration_running, p.filename FROM activities AS a JOIN routes AS r ON a.route_id = r.id JOIN activity_photos AS p ON a.id = p.activity_id WHERE a.user_id = ? AND p.featured = 1 AND YEAR(a.datetime) = ? AND MONTH(a.datetime) = ? ORDER BY a.datetime DESC");
        $getActivitiesByDate->execute([$user->id, $year, $month]);
        $result = $getActivitiesByDate->fetchAll(PDO::FETCH_ASSOC);
        $activity_data = array_map(function ($entry) {
            $root_folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
            require $root_folder . '/actions/blobStorageAction.php';
            $entry['url'] = $blobClient->getBlobUrl('activity-photos', $entry['filename']);
            return $entry;
        }, $result);

        echo json_encode($activity_data);

    }

    if ($_GET['task'] == 'user_inscription_date') {

        $user = new User($_GET['user_id']);

        echo json_encode($user->inscription_date);

    }

}