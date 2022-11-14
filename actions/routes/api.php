<?php
session_start();
// Autoload
require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php'; 
Autoloader::register(); 
require $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {

        if (isset($_GET['route-load'])) {
            $route = new Route($_GET['route-load']);
            echo json_encode($route);
        }

        if (isset($_GET['route-delete'])) {
            $route = new Route($_GET['route-delete']);
            $message = $route->delete();
            echo json_encode($message);
        }
        
        if (isset($_GET['ride-load'])) {
            $ride = new Ride($_GET['ride-load']);
            echo json_encode($ride);
        }
    
        if (isset($_GET['segment-load'])) {
            $segment = new Segment($_GET['segment-load']);
            echo json_encode($segment);
        }

    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$var = json_decode($json, true);

if (is_array($var)) {
    
    if ($var['type'] == 'LineString') {
        $coordinates = new Coordinates($var['coordinates']);
        if (empty($var['name'])) $var['name'] = 'My route';
        if (empty($var['description'])) $var['description'] = '';
        if (!isset($var['tunnels'])) $var['tunnels'] = [];
        if ($var['category'] == 'route') $coordinates->createRoute($connected_user->id, $var['id'], 'route', $var['name'], $var['description'], $var['distance'], $var['elevation'], $var['startplace'], $var['goalplace'], $var['thumbnail'], $var['tunnels']);
        else if ($var['category'] == 'segment') $coordinates->createSegment($connected_user->id, $var['id'], 'segment', $var['name'], $var['description'], $var['distance'], $var['elevation'], $var['startplace'], $var['goalplace'], $var['thumbnail'], $var['tunnels'], $var['rank'], $var['favourite'], $var['seasons'], $var['advice'], $var['specs'], $var['tags']);
        echo json_encode($var);
    }

    if (isset($var['route-load'])) {
        $route = new Route($var['route-load']);
        echo json_encode($route);
    }

}