<?php

///header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

// Query linestring data for aggregation
$query = "SELECT
ST_AsGeoJSON(linestring) AS geometry
FROM linestrings l
INNER JOIN activities a ON a.route_id = l.segment_id
WHERE a.user_id = 11";
$statement = $db->query($query);

$result = $statement->fetchAll(PDO::FETCH_ASSOC);

// Ensure the GeoJSON type is "FeatureCollection"
$featureCollection = [
    'type' => 'FeatureCollection',
    'features' => [],
];

// Convert each LineString to an individual LineString feature
foreach ($result as $row) {
    $geometry = json_decode($row['geometry'], true);

    $lineStringFeature = [
        'type' => 'Feature',
        'geometry' => [
            'type' => 'LineString',
            'coordinates' => $geometry['coordinates'],
        ]
    ];

    // Add the LineString feature to the FeatureCollection
    $featureCollection['features'][] = $lineStringFeature;
}

// Convert the FeatureCollection to line-delimited GeoJSON
$geojson_ld = '';

foreach ($featureCollection['features'] as $feature) {
    $geojson_ld .= json_encode($feature) . "\n";
}

$access_token = getenv('MAPBOX_MTS_TOKEN');
$tileset_id = 'global_heatmap';

// Create a temporary file to store the GeoJSON data
$temp_geojson_file = tempnam(sys_get_temp_dir(), 'geojson');
file_put_contents($temp_geojson_file, $geojson_ld);

$url = "https://api.mapbox.com/tilesets/v1/sources/sisbos/{$tileset_id}?access_token={$access_token}";

// cURL options
$options = [
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => ['file' => new CURLFile($temp_geojson_file)],
    CURLOPT_RETURNTRANSFER => true,
];

// Initialize cURL session
$ch = curl_init();
curl_setopt_array($ch, $options);

// Execute cURL session and capture the response
$response = curl_exec($ch);

echo $response;

// Close cURL session
curl_close($ch);

// Remove the temporary GeoJSON file
unlink($temp_geojson_file);