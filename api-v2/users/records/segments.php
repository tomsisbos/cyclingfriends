<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-authentication.php';

$buffer_width_meters = 100;
$simplification_tolerance = 0.001;

$getClearedSegments = $db->prepare("WITH user_activities AS (
    SELECT
        al.linestring
    FROM activities aa
    JOIN linestrings al ON al.segment_id = aa.route_id
    WHERE aa.user_id = ?
)
SELECT DISTINCT ON (s.id)
    s.id,
    s.name,
    s.rank,
    r.distance,
    r.elevation,
    ST_Length(ST_Intersection(
        l.linestring::geography,
        ST_Buffer(ST_SimplifyPreserveTopology(ua.linestring::geometry, {$simplification_tolerance})::geography, {$buffer_width_meters})
    )) / ST_Length(l.linestring::geography) * 100 AS achievement_percentage
FROM segments s
JOIN linestrings l ON s.route_id = l.segment_id
JOIN routes r ON s.route_id = r.id
CROSS JOIN user_activities ua
WHERE ST_Intersects(l.linestring::geometry, ua.linestring::geometry)
ORDER BY s.id, achievement_percentage DESC");
$getClearedSegments->execute([$_GET['user_id']]);
$segments = $getClearedSegments->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($segments);