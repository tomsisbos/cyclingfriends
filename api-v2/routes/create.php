<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    $route_id = $data->id;
    $category = 'route';
    $linestring = new CFLinestring($data->coordinates);
    $startplace = (new LngLat($data->coordinates[0][0], $data->coordinates[0][1]))->queryGeolocation();
    $goalplace = (new LngLat($data->coordinates[count($data->coordinates) - 1][0], $data->coordinates[count($data->coordinates) - 1][1]))->queryGeolocation();
    $distance = $data->distance;
    $elevation = $data->elevation;
    $name = empty($data->name) ? $startplace->city . 'から' . round($distance, 1) . 'kmのルート' : $data->name;
    $description = $data->description;
    $tunnels = [];
    $linestring->createRoute($user->id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $tunnels);

    echo json_encode([$user->id, $route_id, $category, $name, $description, $distance, $elevation, $startplace, $goalplace, $tunnels]);

}