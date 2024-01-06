<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

$buffer_width_meters = 100;
$simplification_tolerance = 0.001;

$getActivitiesMatching = $db->prepare("WITH segment_linestring AS (
    SELECT
        sll.linestring
    FROM segments sls
    JOIN linestrings sll ON sls.route_id = sll.segment_id
    WHERE sls.id = ?
)

SELECT *
FROM (
    SELECT
        a.id,
        a.title,
        a.datetime as date,
        CASE
            WHEN p.featured = 1 THEN p.filename
            ELSE NULL
        END AS filename,
        ST_Length(ST_Intersection(
            sll.linestring::geography,
            --ST_Buffer(l.linestring, {$buffer_width})
            ST_Buffer(ST_SimplifyPreserveTopology(l.linestring::geometry, {$simplification_tolerance})::geography, {$buffer_width_meters})
        )) / ST_Length(sll.linestring::geography) * 100 AS achievement_percentage
    FROM
        activities a
    JOIN
        linestrings l ON a.route_id = l.segment_id
    LEFT JOIN
        activity_photos p ON a.id = p.activity_id AND p.featured = 1
    JOIN
        segment_linestring sll ON ST_Intersects(l.linestring::geometry, sll.linestring::geometry)
    WHERE
        a.user_id = ?
) AS subquery
WHERE
    achievement_percentage > 0
ORDER BY
    date DESC;");
$getActivitiesMatching->execute([$_GET['segment_id'], $_GET['user_id']]);
$activities = $getActivitiesMatching->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($activities);