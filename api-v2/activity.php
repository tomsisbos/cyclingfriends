<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    $activity = new Activity($data->activity_id);
    if ($user->id == $activity->user_id) {
        $activity->delete();
        echo json_encode(['success' => "アクティビティが削除されました。"]);
    }
    else echo json_encode(['error' => "このアクティビティを削除する権限がありません。"]);

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $activity_id = $_GET['id'];

    if (isset($_GET['photos_number'])) $photos_number = $_GET['photos_number'];
    else $photos_number = 999;

    // Get activity data
    $getActivity = $db->prepare("SELECT
        DISTINCT a.id,
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
        c.city,
        c.prefecture,
        CASE WHEN s.private_zone = 1 THEN true ELSE false END AS private_zone
    FROM
        activities as a
    JOIN
        routes as r ON a.route_id = r.id
    JOIN
        users as u ON a.user_id = u.id
    JOIN
        settings as s ON a.user_id = s.id
    FULL OUTER JOIN
        profile_pictures as pp ON a.user_id = pp.user_id
    FULL OUTER JOIN
        activity_photos as p ON a.id = p.activity_id
    JOIN
        activity_checkpoints as c ON a.id = c.activity_id
    WHERE
        a.id = {$activity_id}");
    $getActivity->execute();
    $activity = $getActivity->fetch(PDO::FETCH_ASSOC);

    // Checkpoints
    $getCheckpoints = $db->prepare("SELECT id, name, distance, story, type, EXTRACT(EPOCH FROM datetime) as datetime, number, elevation, temperature, city, prefecture, ST_X(point::geometry)::double precision as lng, ST_Y(point::geometry)::double precision as lat FROM activity_checkpoints WHERE activity_id = ? ORDER BY number ASC");
    $getCheckpoints->execute([$activity['id']]);
    $activity['checkpoints'] = $getCheckpoints->fetchAll(PDO::FETCH_ASSOC);

    // Photos
    $getPhotos = $db->prepare("SELECT id, filename, EXTRACT(EPOCH FROM datetime) as datetime, elevation,featured, ST_X(point::geometry)::double precision as lng, ST_Y(point::geometry)::double precision as lat FROM activity_photos WHERE activity_id = ? ORDER BY featured::int DESC, RANDOM() LIMIT {$photos_number}");
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

    if (isset($user)) $get_user_grade = "(SELECT grade FROM scenery_grades WHERE user_id = {$user->id} AND scenery_id = s.id) as user_grade,";
    else $get_user_grade = '';

    // Sceneries
    $getSceneries = $db->prepare("SELECT DISTINCT ON (s.id, remoteness)
    s.id,
    s.name,
    s.city,
    s.prefecture,
    COUNT(g.grade) as grades_number,
    AVG(g.grade) as grade,
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

    echo json_encode($activity);

}