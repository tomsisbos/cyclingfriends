<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

$buffer_width_meters = 100;
$simplification_tolerance = 0.001;

$getSegmentsMatching = $db->prepare("WITH activity_linestring AS (
    SELECT
        alli.linestring
    FROM activities ala
    JOIN linestrings alli ON ala.route_id = alli.segment_id
    WHERE ala.id = ?
)

SELECT *
FROM (
    SELECT
        s.id,
        s.name,
        s.rank,
        r.distance,
        r.elevation,
        ST_AsText(ST_SetSRID(l.linestring::geometry, 4326)) AS linestring,
        ST_Length(ST_Intersection(
            l.linestring::geography,
            ST_Buffer(ST_SimplifyPreserveTopology(alli.linestring::geometry, {$simplification_tolerance})::geography, {$buffer_width_meters})
        )) / ST_Length(l.linestring::geography) * 100 AS achievement_percentage
    FROM
        segments s
    JOIN
        linestrings l ON s.route_id = l.segment_id
    JOIN
        routes r ON s.route_id = r.id
    JOIN
        activity_linestring alli ON ST_Intersects(l.linestring::geometry, alli.linestring::geometry)
) AS subquery
WHERE
    achievement_percentage > 0
ORDER BY
    id DESC;");
$getSegmentsMatching->execute([$_GET['activity_id']]);
$segments = $getSegmentsMatching->fetchAll(PDO::FETCH_ASSOC);

foreach ($segments as &$segment) {
    $segment['linestring'] = parseLinestring($segment['linestring']);
}


echo json_encode($segments);

// Function to parse linestring from WKT
function parseLinestring ($linestring) {
    $matches = [];
    preg_match_all('/\(([^)]+)\)/', $linestring, $matches);

    if (isset($matches[1][0])) {
        $coordinates = explode(',', $matches[1][0]);
        
        $result = [];
        foreach ($coordinates as $coordinate) {
            list($lng, $lat) = array_map('floatval', explode(' ', trim($coordinate)));
            $result[] = ['lng' => $lng, 'lat' => $lat];
        }

        return $result;
    }

    return [];
}