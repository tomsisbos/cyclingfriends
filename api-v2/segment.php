<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

$scenery_max_distance = 300;

$getSegment = $db->prepare("WITH sceneries_within_distance AS (
    SELECT
        seg.id AS segment_id,
        scen.id AS scenery_id,
        scen.name AS scenery_name,
        scen.popularity AS scenery_popularity,
        scen.point AS point
    FROM segments seg
	JOIN linestrings lin ON seg.route_id = lin.segment_id
    JOIN sceneries scen ON ST_DWithin(lin.linestring::geometry, scen.point, {$scenery_max_distance})
)

SELECT
    s.id,
    s.route_id,
    s.rank,
    s.name,
    s.advised,
    s.rating,
    s.grades_number,
    s.description,
    s.advice_name,
    s.advice_description,
    s.popularity,
    s.spec_offroad,
    s.spec_rindo,
    s.spec_cyclinglane,
    s.spec_cyclingroad,
    r.distance,
    r.elevation,
    r.startplace,
    r.goalplace,
    r.thumbnail_filename,
    r.author_id,
    ST_AsText(ST_SetSRID(l.linestring::geometry, 4326)) AS linestring,
    COALESCE(array_to_string(array_agg(DISTINCT t.tag), ','), '') as tags,
    jsonb_agg(
        DISTINCT jsonb_build_object(
            'number', ss.number,
            'period_start_month', ss.period_start_month,
            'period_start_detail', ss.period_start_detail,
            'period_end_month', ss.period_end_month,
            'period_end_detail', ss.period_end_detail,
            'description', ss.description
        )
    ) AS seasons,
    jsonb_agg(
        jsonb_build_object(
            'scenery_id', swd.scenery_id,
            'scenery_name', swd.scenery_name,
            'filename', scp.filename,
            'date', scp.date,
            'user_id', scp.user_id,
            'popularity', swd.scenery_popularity,
            'lng', ST_X(swd.point::geometry)::double precision,
            'lat', ST_Y(swd.point::geometry)::double precision
        )
    ) AS scenery_photos
FROM segments s
LEFT JOIN segment_seasons ss ON s.id = ss.segment_id
LEFT JOIN sceneries_within_distance swd ON s.id = swd.segment_id
LEFT JOIN scenery_photos scp ON swd.scenery_id = scp.scenery_id
JOIN routes AS r ON s.route_id = r.id
JOIN linestrings AS l ON r.id = l.segment_id
LEFT JOIN tags AS t ON t.object_type = 'segment' AND t.object_id = s.id
WHERE s.id = ?
GROUP BY s.id, r.distance, r.elevation, r.startplace, r.goalplace, r.thumbnail_filename, r.author_id, l.linestring
ORDER BY popularity DESC, RANDOM() ASC");
$getSegment->execute([$_GET['id']]);
$segment = $getSegment->fetch(PDO::FETCH_ASSOC);

// Convert linestring to array of coordinates
$segment['linestring'] = parseLinestring($segment['linestring']);
$segment['seasons'] = json_decode($segment['seasons'], true);
$segment['scenery_photos'] = json_decode($segment['scenery_photos'], true);

echo json_encode($segment);

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