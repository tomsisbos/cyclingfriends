<?php

require_once '../includes/api-public-head.php';

// Query linestring data for aggregation
$query = "SELECT
    ST_AsGeoJSON(l.linestring) AS geometry,
    a.id AS activity_id
    FROM linestrings l
    INNER JOIN activities a ON a.route_id = l.segment_id
    WHERE a.privacy = 'public'
    --WHERE a.user_id = 1
"; // Modify query accordingly
$statement = $db->query($query);

$access_token = getenv('MAPBOX_MTS_TOKEN');
$tileset_id = 'global_heatmap';

// Initialize cURL session
$ch = curl_init();

// Create a temporary file to store the GeoJSON data
$temp_geojson_file = tempnam(sys_get_temp_dir(), 'geojson');
$geojsonFileHandle = fopen($temp_geojson_file, 'w');

// Fetch and process data one row at a time
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
    $geometry = json_decode($row['geometry'], true);
    $activity_id = $row['activity_id'];

    $feature = [
        'type' => 'Feature',
        'geometry' => [
            'type' => 'LineString',
            'coordinates' => $geometry['coordinates'],
        ],
        'properties' => [
            'activity_id' => $activity_id
        ]
    ];

    // Convert the Feature to line-delimited GeoJSON and write to the file
    fwrite($geojsonFileHandle, json_encode($feature) . "\n");
}

// Close the file handle
fclose($geojsonFileHandle);

// Build cURL options
$url = "https://api.mapbox.com/tilesets/v1/sources/sisbos/{$tileset_id}?access_token={$access_token}";
$options = [
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => ['file' => new CURLFile($temp_geojson_file)],
    CURLOPT_RETURNTRANSFER => true,
];

// Set cURL options and execute the session
curl_setopt_array($ch, $options);
$response = curl_exec($ch);

// Output the response or handle it as needed
echo $response;

// Remove the temporary GeoJSON file
unlink($temp_geojson_file);

// Close cURL session
curl_close($ch);

// Close database connection
$statement->closeCursor();

?>