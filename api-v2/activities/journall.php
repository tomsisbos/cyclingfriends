<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $user_id = $_GET['user_id'];

    $month = $_GET['month'];
    
    $year = $_GET['year'];

    if (isset($_GET['include_private'])) $include_private = $_GET['include_private'];
    $photos_number = $_GET['photos_number'];

    // If include private is specified prepare where clause
    if (isset($include_private) AND $include_private == true) $restrict_to_public = '';
    else $restrict_to_public = " AND a.privacy = 'public'";
    
    // Get activity data
    $getActivities = $db->prepare("WITH Activities AS (
        SELECT
            a.id,
            a.title,
            a.user_id as author_id,
            a.privacy,
            a.bike_id,
            r.id as route_id,
            r.distance,
            r.thumbnail_filename as thumbnail,
            u.login as author_login,
            u.default_profilepicture_id as default_propic_id,
            pp.filename as author_propic,
            a.datetime::date as date,
            EXTRACT(YEAR FROM a.datetime::date) as year,
            EXTRACT(MONTH FROM a.datetime::date) as month,
            CASE 
                WHEN EXTRACT(DOW FROM a.datetime::date) = 0 THEN 7
                ELSE EXTRACT(DOW FROM a.datetime::date)
            END AS week_day,
            EXTRACT(WEEK FROM a.datetime::date) - EXTRACT(WEEK FROM DATE_TRUNC('month', a.datetime::date)) + 1 as month_week,
            c.city,
            c.prefecture,
            CASE WHEN s.private_zone = 1 THEN true ELSE false END AS private_zone,
            COUNT(p.id) as photos_number,
            LENGTH(c.story) as story_length
        FROM
            activities as a
        JOIN
            routes as r ON a.route_id = r.id
        JOIN
            users as u ON a.user_id = u.id
        FULL OUTER JOIN
            settings as s ON a.user_id = s.id
        FULL OUTER JOIN
            profile_pictures as pp ON a.user_id = pp.user_id
        FULL OUTER JOIN
            activity_photos as p ON a.id = p.activity_id
        JOIN
            activity_checkpoints as c ON a.id = c.activity_id
        WHERE
            c.number = 0 AND
            EXTRACT(YEAR FROM a.datetime::date) = {$year} AND
            EXTRACT(MONTH FROM a.datetime::date) = {$month} AND
            a.user_id = {$user_id} {$restrict_to_public}
        GROUP BY
            a.id, a.title, a.user_id, a.privacy, a.bike_id, r.id, r.distance,
            r.thumbnail_filename, u.login, u.default_profilepicture_id, pp.filename,
            a.datetime::date, c.city, c.prefecture, c.story, s.private_zone
    )

    SELECT *
    FROM Activities
    ORDER BY
        date DESC, 
        CASE 
            WHEN photos_number > 0 THEN 1 
            WHEN story_length > 0 THEN 2 
            ELSE 3 
        END ASC,
        distance DESC");
    $getActivities->execute();
    $activities = $getActivities->fetchAll(PDO::FETCH_ASSOC);

    // Append necessary data
    $user = isset($user) ? $user : null;
    $activities = array_map(function ($activity) use ($db, $user, $photos_number) {

        // Checkpoints
        $getCheckpoints = $db->prepare("SELECT id, name, distance, story, type, EXTRACT(EPOCH FROM datetime) as datetime, number, elevation, temperature, city, prefecture, ST_X(point::geometry)::double precision as lng, ST_Y(point::geometry)::double precision as lat FROM activity_checkpoints WHERE activity_id = ? ORDER BY number ASC");
        $getCheckpoints->execute([$activity['id']]);
        $activity['checkpoints'] = $getCheckpoints->fetchAll(PDO::FETCH_ASSOC);

        // Photos
        $getPhotos = $db->prepare("SELECT id, filename, EXTRACT(EPOCH FROM datetime) as datetime, elevation, featured, ST_X(point::geometry)::double precision as lng, ST_Y(point::geometry)::double precision as lat, privacy FROM activity_photos WHERE activity_id = ? ORDER BY featured::int DESC, RANDOM() LIMIT {$photos_number}");
        $getPhotos->execute([$activity['id']]);
        $activity['photos'] = $getPhotos->fetchAll(PDO::FETCH_ASSOC);

        // Comments
        $getComments = $db->prepare("SELECT c.id, c.user_id, c.content, c.time, u.default_profilepicture_id as default_propic_id, pp.filename as propic FROM activity_comments as c JOIN users as u ON c.user_id = u.id FULL OUTER JOIN profile_pictures as pp ON c.user_id = pp.user_id WHERE c.entry_id = ? ORDER BY c.time ASC");
        $getComments->execute([$activity['id']]);
        $activity['comments'] = $getComments->fetchAll(PDO::FETCH_ASSOC);

        // Likes 
        $getLikes = $db->prepare("SELECT
            u.id as user_id,
            u.login,
            u.default_profilepicture_id as default_propic_id, 
            pp.filename as propic
        FROM activity_likes al
        JOIN users u ON u.id = al.user_id
        JOIN profile_pictures pp ON pp.user_id = u.id
        WHERE al.activity_id = ?");
        $getLikes->execute([$activity['id']]);
        $activity['likes'] = $getLikes->fetchAll(PDO::FETCH_ASSOC);

        if (isset($user)) $get_user_grade = "(SELECT grade FROM scenery_grades WHERE user_id = {$user->id} AND scenery_id = s.id)::double precision as user_grade,";
        else $get_user_grade = '';

        // Sceneries
        $getSceneries = $db->prepare("SELECT DISTINCT ON (s.id, remoteness)
            s.id,
            s.name,
            s.city,
            s.prefecture,
            COUNT(g.grade)::numeric as grades_number,
            AVG(g.grade)::double precision as rating,
            {$get_user_grade}
            ST_Distance(point, (SELECT linestring FROM linestrings WHERE segment_id = {$activity['route_id']})) as remoteness,
            p.filename,
            ST_X(point::geometry)::double precision as lng,
            ST_Y(point::geometry)::double precision as lat
        FROM sceneries as s
        INNER JOIN scenery_photos as p ON s.id = p.scenery_id
        FULL OUTER JOIN scenery_grades as g ON s.id = g.scenery_id
        WHERE ST_DWithin(
            (SELECT linestring FROM linestrings WHERE segment_id = {$activity['route_id']} LIMIT 1),
            point,
            300
        )
        GROUP BY s.id, p.filename
        ORDER BY s.id, remoteness DESC");
        $getSceneries->execute();
        $activity['sceneries'] = $getSceneries->fetchAll(PDO::FETCH_ASSOC);

        return $activity;
    }, $activities);

    echo json_encode($activities);

}