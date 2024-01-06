<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

if ($_GET['distance']) $buffer_width_meters = $_GET['distance'];
else $buffer_width_meters = 300;

$db->exec("SET statement_timeout = 10000"); // Set a 10 seconds timeout

if (isset($user)) $get_user_grade = "(SELECT grade FROM scenery_grades WHERE user_id = {$user->id} AND scenery_id = s.id) as user_grade,";
else $get_user_grade = '';

$getSceneriesMatching = $db->prepare("WITH scenery_grades_counts AS (
    SELECT
        scenery_id,
        COUNT(grade) as grades_number
    FROM
        scenery_grades
    GROUP BY
        scenery_id
)

SELECT DISTINCT ON (distance, s.id)
    s.id,
    s.name,
    s.city,
    s.prefecture,
    AVG(g.grade)::double precision as rating,
    COALESCE(c.grades_number, 0) as grades_number,
    {$get_user_grade}
    ST_X(s.point::geometry) as lng,
    ST_Y(s.point::geometry) as lat,
    p.filename as uri,
    ST_Length(
        ST_Transform(
            ST_LineSubstring(
                ST_Transform(li.linestring::geometry, 3857),
                0.0,
                ST_LineLocatePoint(ST_Transform(li.linestring::geometry, 3857), ST_Transform(s.point::geometry, 3857))
            ),
            3857
        )
    ) / 1000 AS distance
FROM
    sceneries AS s
INNER JOIN
    scenery_photos AS p ON s.id = p.scenery_id
INNER JOIN
    linestrings li ON ST_DWithin(li.linestring::geography, s.point::geography, {$buffer_width_meters})
INNER JOIN
    activities ac ON ac.route_id = li.segment_id
LEFT JOIN
    scenery_grades_counts AS c ON s.id = c.scenery_id
LEFT JOIN
    scenery_grades AS g ON s.id = g.scenery_id
WHERE
    ac.id = ?
GROUP BY
    s.id, s.name, s.city, s.prefecture, lng, lat, uri, distance, c.grades_number
ORDER BY
    distance, s.id");
$getSceneriesMatching->execute([$_GET['activity_id']]);
$sceneries = $getSceneriesMatching->fetchAll(PDO::FETCH_ASSOC);

require substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT']))) . '/actions/blobStorage.php';
$sceneries = array_map(function ($scenery) use ($blobClient) {
    $scenery['uri'] = $blobClient->getBlobUrl('scenery-photos', $scenery['uri']);
    return $scenery;
}, $sceneries);

echo json_encode($sceneries);