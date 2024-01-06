<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-authentication.php';

$simplification_tolerance = 0.001;
$distance_tolerance = 200; // Meters

$getClearedSceneries = $db->prepare("WITH user_activities AS (
    SELECT
        a.title,
        a.datetime,
        ST_SnapToGrid(linestring::geometry, {$simplification_tolerance})::geography as linestrings
    FROM 
        activities a
        JOIN linestrings l ON a.route_id = l.segment_id
    WHERE 
        a.user_id = ?
)

SELECT 
    s.id,
    s.name,
    s.city,
    s.prefecture,
    MIN(p.filename) as uri,
    MIN(ua.datetime) as datetime,
    jsonb_agg(DISTINCT ua.title) AS activities
FROM 
    sceneries s
INNER JOIN
    scenery_photos AS p ON s.id = p.scenery_id
CROSS JOIN 
    user_activities ua
WHERE 
    ST_DWithin(ua.linestrings, s.point, {$distance_tolerance})
GROUP BY
    s.id, s.name
ORDER BY
	datetime DESC");
$getClearedSceneries->execute([$_GET['user_id']]);
$sceneries = $getClearedSceneries->fetchAll(PDO::FETCH_ASSOC);

foreach ($sceneries as &$scenery) {
    $scenery['activities'] = json_decode($scenery['activities'], true);
}

echo json_encode($sceneries);