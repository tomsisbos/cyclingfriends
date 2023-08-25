<?php

require '../../includes/api-head.php';
    
if (isset($_GET)) {
        
    if ($_GET['task'] == 'rides') {

        $limit = $_GET['number'];

        $getRides = $db->prepare("
            SELECT DISTINCT
                r.id, r.name, r.date, r.description, c.filename as featured_image, r.entry_start, r.entry_end
            FROM rides as r
            JOIN ride_checkpoints as c
            ON r.id = c.ride_id AND c.featured = 1
            WHERE
                r.author_id = 2 AND
                r.privacy = 'public' AND
                r.entry_end > NOW()
            ORDER BY r.date ASC
            LIMIT {$limit}
        ");
        $getRides->execute();
        $rides = $getRides->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($rides);

    } else if ($_GET['task'] == 'news') {

        $getNews = $db->prepare("
            SELECT id, title, content, type, 
                (CASE
                    WHEN type = 'dev' THEN '開発'
                    WHEN type = 'general' THEN '一般'
                    ELSE '一般'
                END) as typestring,
            datetime::date as date
            FROM posts
            ORDER BY datetime DESC
            LIMIT 1
        ");
        $getNews->execute();
        $news = $getNews->fetch(PDO::FETCH_ASSOC);

        echo json_encode($news);

    } else if ($_GET['task'] == 'activities') {

        $limit = $_GET['activities_number'];
        $photos_number = $_GET['photos_number'];

        // Get activity data
        $getActivities = $db->prepare("
            SELECT DISTINCT
                a.id,
                a.title,
                a.user_id as author_id,
                r.id as route_id,
                r.distance,
                r.thumbnail_filename as thumbnail,
                u.login as author_login,
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
                activity_photos as p ON a.id = p.activity_id
            JOIN
                activity_checkpoints as c ON a.id = c.activity_id
            JOIN
                profile_pictures as pp ON a.user_id = pp.user_id
            WHERE
                c.number = 0 AND 
                r.distance > 20
            ORDER BY a.datetime DESC
            LIMIT {$limit}
        ");
        $getActivities->execute();
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
}