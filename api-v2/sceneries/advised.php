<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-head.php';

$simplification_tolerance = 0.004;
$distance_tolerance_in_meters = 300;
$field_scope_in_kilometers = 50;
$distance_in_meters_to_be_considered_as_nearby = 5000;
$number_of_latest_activities_to_include_in_computation = 14;

if (isset($_GET['user_id'])) $user = new User($_GET['user_id']);
else if (!isset($user)) $user = getConnectedUser();

$getAdvisedSceneries = $db->prepare("WITH user_activities AS (
    SELECT ST_Collect(ST_SnapToGrid(linestring::geometry, {$simplification_tolerance})::geometry) AS linestrings
    FROM activities a
    JOIN linestrings l ON a.route_id = l.segment_id
    WHERE a.user_id = :user_id
),

latest_user_activities AS (
    SELECT id
    FROM activities
    WHERE user_id = :user_id
    ORDER BY datetime DESC
    LIMIT {$number_of_latest_activities_to_include_in_computation}
),

checkpoint_counts AS (
    SELECT
        ac1.id AS checkpoint_id,
		ac1.point,
        a.title AS activity,
        COUNT(ac2.id) AS nearby_count
    FROM activity_checkpoints ac1
    JOIN activity_checkpoints ac2 ON ac1.id <> ac2.id
    JOIN activities a ON ac1.activity_id = a.id
    WHERE ac1.number = 0
        AND ac2.number = 0
        AND ac1.activity_id IN (SELECT id FROM latest_user_activities)
        AND ac2.activity_id IN (SELECT id FROM latest_user_activities)
        AND ST_DWithin(ac1.point::geography, ac2.point::geography, {$distance_in_meters_to_be_considered_as_nearby})
    GROUP BY ac1.id, a.title
    ORDER BY nearby_count DESC
    LIMIT 1
),

filtered_sceneries AS (
    SELECT 
        s.id,
        s.name,
        s.city,
        s.prefecture,
        MIN(p.filename) AS uri,
        s.point
    FROM sceneries s
    INNER JOIN scenery_photos AS p ON s.id = p.scenery_id
    CROSS JOIN checkpoint_counts cc
    WHERE ST_DWithin(s.point, cc.point, {$field_scope_in_kilometers} * 1000)
    GROUP BY s.id, s.name, s.city, s.prefecture, s.point
)

SELECT 
    fs.id,
    fs.name,
    fs.city,
    fs.prefecture,
    fs.uri,
    fs.point
FROM filtered_sceneries fs
CROSS JOIN user_activities ua
WHERE NOT ST_DWithin(ua.linestrings, fs.point, {$distance_tolerance_in_meters}, false)");
$getAdvisedSceneries->execute([':user_id' => 44]);
$sceneries = $getAdvisedSceneries->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($sceneries);