<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

$distance_in_meters_to_be_considered_as_nearby = 5000;
$number_of_latest_activities_to_include_in_computation = 14;

$getUserLocation = $db->prepare("WITH latest_user_activities AS (
	SELECT id
	FROM activities
	WHERE user_id = ?
	ORDER BY datetime DESC
	LIMIT {$number_of_latest_activities_to_include_in_computation}
),

checkpoint_counts AS (
    SELECT
        ac1.id AS checkpoint_id,
		(SELECT title FROM activities WHERE id = ac1.activity_id) AS activity,
        COUNT(ac2.id) AS nearby_count
    FROM
        activity_checkpoints ac1
        JOIN activity_checkpoints ac2 ON ac1.id <> ac2.id
    WHERE
		ac1.number = 0
		AND ac2.number = 0
		AND ac1.activity_id IN (SELECT id FROM latest_user_activities)
		AND ac2.activity_id IN (SELECT id FROM latest_user_activities)
        AND ST_DWithin(ac1.point::geography, ac2.point::geography, {$distance_in_meters_to_be_considered_as_nearby})
    GROUP BY
        ac1.id
)
		
SELECT ST_AsText(ac.point) FROM activity_checkpoints ac JOIN checkpoint_counts cc ON ac.id = cc.checkpoint_id ORDER BY cc.nearby_count DESC LIMIT 1");
$getUserLocation->execute([$_GET['user_id']]);
$point = $getUserLocation->fetch(PDO::FETCH_COLUMN);

$coordinates = new LngLat();
$coordinates->fromWKT($point);

echo json_encode($coordinates);