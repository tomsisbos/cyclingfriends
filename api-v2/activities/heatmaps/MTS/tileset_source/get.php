<?php

require_once '../includes/api-public-head.php';

$access_token = getenv('MAPBOX_MTS_TOKEN');
$tileset_id = 'global_heatmap';

$url = "https://api.mapbox.com/tilesets/v1/sources/sisbos/{$tileset_id}?access_token={$access_token}";

// cURL options
$options = [
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => 'GET',
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