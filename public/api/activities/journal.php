<?php

require '../../../includes/api-head.php';

if (isset($_GET)) {

    if ($_GET['task'] == 'first_activity_date') {

        $getFirstActivityDate = $db->prepare("SELECT datetime FROM activities WHERE user_id = ? ORDER BY datetime ASC LIMIT 1");
        $getFirstActivityDate->execute([$_GET['user_id']]);
        if ($getFirstActivityDate->rowCount() > 0) $first_activity_date = $getFirstActivityDate->fetch(PDO::FETCH_COLUMN);
        else $first_activity_date = (new DateTime('now'))->format('Y-m-d');

        echo json_encode($first_activity_date);

    }

    if ($_GET['task'] == 'activity_data') {

        $user = new User($_GET['user_id']);
        $year = intval($_GET['year']);
        $month = intval($_GET['month']);

        if (!isSessionActive() || getConnectedUser()->id != $user->id) $privacy_contition = "AND a.privacy = 'public' ";
        else $privacy_contition = '';

        $getActivitiesByDate = $db->prepare("SELECT a.id, a.title, a.datetime, r.distance, a.duration_running, a.privacy, (
            SELECT
                filename
            FROM
                activity_photos AS p 
            WHERE  
                CASE
                    WHEN a.id IN (SELECT DISTINCT a.id FROM activity_photos AS p JOIN activities AS a ON a.id = p.activity_id WHERE p.activity_id = a.id AND p.featured = 1) THEN activity_id = a.id AND featured = 1
                    ELSE activity_id = a.id
                END
            LIMIT 1
        ) AS filename FROM activities AS a JOIN routes AS r ON a.route_id = r.id WHERE a.user_id = ? " .$privacy_contition. "AND YEAR(a.datetime) = ? AND MONTH(a.datetime) = ? ORDER BY a.datetime DESC");
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

}