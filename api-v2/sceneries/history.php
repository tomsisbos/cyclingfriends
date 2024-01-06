<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

$max_distance = 300; // meters

$db->exec("SET statement_timeout = 10000"); // Set a 10 seconds timeout

$getActivitiesMatching = $db->prepare("WITH scenery_point AS (
    SELECT
        point
    FROM
        sceneries
    WHERE
        id = ?
)
SELECT
    a.id,
    a.title,
    a.datetime AS date,
    CASE
        WHEN p.featured = 1 THEN p.filename
        ELSE NULL
    END AS filename
FROM
    activities AS a
JOIN
    linestrings AS l ON a.route_id = l.segment_id
LEFT JOIN
    activity_photos AS p ON a.id = p.activity_id AND p.featured = 1
CROSS JOIN
    scenery_point as sp
WHERE
    a.user_id = ?
    AND ST_DWithin(
		l.linestring::geography,
        sp.point::geography,
		{$max_distance}
    )
ORDER BY
    date DESC");
$getActivitiesMatching->execute([$_GET['scenery_id'], $_GET['user_id']]);
$activities = $getActivitiesMatching->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($activities);