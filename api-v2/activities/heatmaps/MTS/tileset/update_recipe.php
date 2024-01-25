<?php

$recipe = [
    'version' => 1,
    'layers' => [
        'global_heatmap' => [
            'source' => "mapbox://tileset-source/sisbos/global_heatmap",
            'minzoom' => 0,
            'maxzoom' => 10
        ],
    ],
];


$access_token = getenv('MAPBOX_MTS_TOKEN');
$name = 'global_heatmap';
$tileset_id = 'sisbos.' .$name;

$url = "https://api.mapbox.com/tilesets/v1/{$tileset_id}/recipe?access_token={$access_token}";

// cURL options
$options = [
    CURLOPT_URL => $url,
    CURLOPT_CUSTOMREQUEST => 'PATCH',
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($recipe, JSON_UNESCAPED_SLASHES),
    CURLOPT_RETURNTRANSFER => true,
];

// Initialize cURL session
$ch = curl_init();
curl_setopt_array($ch, $options);

// Execute cURL session and capture the response
$response = curl_exec($ch);

echo $response;