<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

// Bounds are expected to be set as an array like [Xlng, Xlat, Ylng, Ylat]
$getSegmentsFromBoundingBox = $db->prepare("WITH segments_ranked AS (
    SELECT
        s.id,
        s.route_id,
        s.rank,
        s.advised,
        s.rating,
        s.grades_number,
        s.name,
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
        COALESCE(array_to_string(array_agg(DISTINCT t.tag), ','), '') as tags
    FROM segments AS s
    JOIN routes AS r ON s.route_id = r.id
    JOIN linestrings AS l ON r.id = l.segment_id
    LEFT JOIN tags AS t ON t.object_type = 'segment' AND t.object_id = s.id
    WHERE ST_Intersects(l.linestring::geometry, ST_MakeEnvelope(?, ?, ?, ?, 4326))
    GROUP BY s.id, s.route_id, s.rank, s.rating, s.grades_number, s.advised, s.name, s.popularity, s.description, s.advice_name, s.advice_description, s.spec_offroad, s.spec_rindo, s.spec_cyclinglane, s.spec_cyclingroad, r.distance, r.elevation, r.startplace, r.goalplace, r.thumbnail_filename, r.author_id, linestring
),

sceneries_within_distance AS (
    SELECT
        s.id AS segment_id,
        sc.id AS scenery_id,
        sc.name AS scenery_name,
        sc.popularity AS scenery_popularity,
        sc.point AS point
    FROM segments_ranked s
    JOIN sceneries sc ON ST_DWithin(s.linestring::geometry, sc.point, 300)  -- Replace '?' with your desired distance
)

SELECT
    sr.id,
    sr.route_id,
    sr.rank,
    sr.name,
    sr.advised,
    sr.rating,
    sr.grades_number,
    sr.description,
    sr.advice_name,
    sr.advice_description,
    sr.popularity,
    sr.spec_offroad,
    sr.spec_rindo,
    sr.spec_cyclinglane,
    sr.spec_cyclingroad,
    sr.distance,
    sr.elevation,
    sr.startplace,
    sr.goalplace,
    sr.thumbnail_filename,
    sr.author_id,
    sr.linestring,
    sr.tags,
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
FROM segments_ranked sr
LEFT JOIN segment_seasons ss ON sr.id = ss.segment_id
LEFT JOIN sceneries_within_distance swd ON sr.id = swd.segment_id
LEFT JOIN scenery_photos scp ON swd.scenery_id = scp.scenery_id
GROUP BY
    sr.id,
    sr.route_id,
    sr.rank,
    sr.name,
    sr.advised,
    sr.rating,
    sr.grades_number,
    sr.description,
    sr.advice_name,
    sr.advice_description,
    sr.popularity,
    sr.spec_offroad,
    sr.spec_rindo,
    sr.spec_cyclinglane,
    sr.spec_cyclingroad,
    sr.distance,
    sr.elevation,
    sr.startplace,
    sr.goalplace,
    sr.thumbnail_filename,
    sr.author_id,
    sr.linestring,
    sr.tags
ORDER BY popularity DESC, RANDOM() ASC
LIMIT ?;");
$getSegmentsFromBoundingBox->execute([$_GET['bounds'][0], $_GET['bounds'][1], $_GET['bounds'][2], $_GET['bounds'][3], $_GET['limit']]);
$segments = $getSegmentsFromBoundingBox->fetchAll(PDO::FETCH_ASSOC);

// Convert linestring to array of coordinates
foreach ($segments as &$segment) {
    $segment['linestring'] = parseLinestring($segment['linestring']);
    $segment['seasons'] = json_decode($segment['seasons'], true);
    $segment['scenery_photos'] = json_decode($segment['scenery_photos'], true);
}

echo json_encode($segments);

// Function to parse linestring from WKT
function parseLinestring($linestring) {
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