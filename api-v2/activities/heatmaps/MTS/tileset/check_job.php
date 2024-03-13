<?php

$access_token = getenv('MAPBOX_MTS_TOKEN');
$name = 'global_heatmap';
$tileset_id = 'sisbos.' .$name;
$job_id = 'cltoclyzw001408l79v4ed1jo';

$url = "https://api.mapbox.com/tilesets/v1/{$tileset_id}/jobs/{$job_id}?access_token={$access_token}";

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