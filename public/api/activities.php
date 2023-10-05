<?php

require '../../includes/api-head.php';

if (isset($_GET['user']) || isset($_GET['my-activities'])) {

    $limit = $_GET['activities_number'];
    $offset = $_GET['offset'];
    $photos_number = $_GET['photos_number'];
    if (isset($_GET['user'])) $user_id = $_GET['user'];
    else if (isset($_GET['my-activities'])) $user_id = getConnectedUser()->id;

    // Get activity data
    $getActivities = $db->prepare("
        SELECT
            DISTINCT a.id,
            a.title,
            a.user_id as author_id,
            r.id as route_id,
            r.distance,
            r.thumbnail_filename as thumbnail,
            u.login as author_login,
            u.default_profilepicture_id as default_propic_id,
            pp.filename as author_propic,
            a.datetime::date as date,
            c.city,
            c.prefecture
        FROM
            activities as a
        JOIN
            routes as r ON a.route_id = r.id
        JOIN
            users as u ON a.user_id = u.id
        FULL OUTER JOIN
            profile_pictures as pp ON a.user_id = pp.user_id
        FULL OUTER JOIN
            activity_photos as p ON a.id = p.activity_id
        JOIN
            activity_checkpoints as c ON a.id = c.activity_id
        WHERE
            a.user_id = ? AND
            c.number = 0
        ORDER BY a.datetime DESC
        LIMIT {$limit} OFFSET {$offset}
    ");
    $getActivities->execute([$user_id]);
    $activities = $getActivities->fetchAll(PDO::FETCH_ASSOC);

    // Append necessary data
    $activities = array_map(function ($activity) use ($db, $photos_number) {

        // Checkpoints
        $getCheckpoints = $db->prepare("SELECT id, name, distance, story FROM activity_checkpoints WHERE activity_id = ?");
        $getCheckpoints->execute([$activity['id']]);
        $activity['checkpoints'] = $getCheckpoints->fetchAll(PDO::FETCH_ASSOC);

        // Photos
        $getPhotos = $db->prepare("SELECT filename FROM activity_photos WHERE activity_id = ? ORDER BY featured::int DESC, RANDOM() LIMIT {$photos_number}");
        $getPhotos->execute([$activity['id']]);
        $activity['photos'] = $getPhotos->fetchAll(PDO::FETCH_COLUMN);

        // Comments
        $getComments = $db->prepare("SELECT c.user_id, c.content, u.default_profilepicture_id as default_propic_id, pp.filename as propic FROM activity_comments as c JOIN users as u ON c.user_id = u.id JOIN profile_pictures as pp ON c.user_id = pp.user_id WHERE c.entry_id = ? ORDER BY c.time DESC");
        $getComments->execute([$activity['id']]);
        $activity['comments'] = $getComments->fetchAll(PDO::FETCH_ASSOC);

        // Likes 
        $getLikes = $db->prepare("SELECT user_id FROM activity_likes WHERE activity_id = ?");
        $getLikes->execute([$activity['id']]);
        $activity['likes'] = $getLikes->fetchAll(PDO::FETCH_COLUMN);

        // Sceneries
        $getSceneries = $db->prepare("
            SELECT
                id,
                name,
                ST_Distance(point, (SELECT linestring FROM linestrings WHERE segment_id = {$activity['route_id']})) as remoteness
            FROM
                sceneries
            WHERE
                ST_DWithin(
                    (
                        SELECT linestring FROM linestrings WHERE segment_id = {$activity['route_id']}),
                        point,
                        300
                    )
            ORDER BY 
                remoteness DESC
        ");
        $getSceneries->execute();
        $activity['sceneries'] = $getSceneries->fetchAll(PDO::FETCH_ASSOC);

        return $activity;
    }, $activities);

    echo json_encode($activities);
}