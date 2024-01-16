<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if (isset($_GET['user_id'])) {
        if (is_numeric($_GET['user_id'])) $user_id_to_restrict = $_GET['user_id'];
        else $user_id_to_restrict = getConnectedUser()->id;
    }

    if (!isset($user) AND isset(getConnectedUser()->id)) $user = getConnectedUser();

    $limit = $_GET['activities_number'];
    $offset = $_GET['offset'];
    if (isset($_GET['include_private'])) $include_private = $_GET['include_private'];
    $photos_number = $_GET['photos_number'];
    if (isset($_GET['activity_min_distance'])) $activity_min_distance = $_GET['activity_min_distance'];
    else $activity_min_distance = 0;

    // If user id is specified prepare where clause
    if (isset($user_id_to_restrict)) $restrict_to_user_id_query = " AND a.user_id = {$user_id_to_restrict}";
    else $restrict_to_user_id_query = '';

    // If include private is specified prepare where clause
    if (isset($include_private) AND $include_private == true) $restrict_to_public = '';
    else $restrict_to_public = " AND a.privacy = 'public'";
    
    // Get activity data
    $getActivities = $db->prepare("SELECT *
    FROM (
        SELECT
            DISTINCT a.id,
            a.title,
            a.user_id as author_id,
            a.privacy,
            a.bike_id,
            a.datetime::date as date,
            a.posting_date,
            r.id as route_id,
            r.distance,
            r.thumbnail_filename as thumbnail,
            u.login as author_login,
            u.default_profilepicture_id as default_propic_id,
            pp.filename as author_propic,
            c.city,
            c.prefecture,
            c.story,
            (SELECT
                    COUNT(id)
                FROM followers
                WHERE following_id = {$user->id} AND followed_id = a.user_id) > 0 as following,
            ROW_NUMBER() OVER (ORDER BY (SELECT
                COUNT(id)
            FROM followers
            WHERE following_id = {$user->id} AND followed_id = a.user_id OR a.user_id = {$user->id}) > 0 DESC, a.posting_date DESC) AS subquery_row_number
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
        JOIN
            followers as f ON a.user_id = f.following_id
        WHERE
            r.distance > {$activity_min_distance} AND
            c.number = 0 {$restrict_to_user_id_query} {$restrict_to_public}
        GROUP BY a.id, r.id, u.login, u.default_profilepicture_id, pp.filename, c.city, c.prefecture, c.story, a.posting_date
        HAVING COUNT(p.id) > 0 OR LENGTH(c.story) > 0 OR r.distance > 80
    ) AS subquery
    ORDER BY 
        CASE
            WHEN subquery.subquery_row_number <= 5 THEN subquery.subquery_row_number
        END
        ASC, subquery.posting_date DESC
    LIMIT {$limit} OFFSET {$offset}");
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
        (SELECT linestring FROM linestrings WHERE segment_id = {$activity['route_id']}),
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