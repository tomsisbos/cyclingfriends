<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {

        if (isset($_GET['route-load'])) {
            $route = new Route($_GET['route-load']);
            $route->coordinates = $route->getLinestring()->coordinates;
            echo json_encode($route);
        }

        if (isset($_GET['route-load-from-ride'])) {
            $ride = new Ride($_GET['route-load-from-ride']);
            $ride->checkpoints = $ride->getCheckpoints();
            $route = $ride->getRoute();
            if ($route) $route->coordinates = $route->getLinestring()->coordinates;
            echo json_encode($route);
        }

        if (isset($_GET['route-delete'])) {
            $route = new Route($_GET['route-delete']);
            $message = $route->delete();
            echo json_encode($message);
        }
        
        if (isset($_GET['ride-load'])) {
            $ride = new Ride($_GET['ride-load']);
            $ride->checkpoints = $ride->getCheckpoints();
            $ride->route = $ride->getRoute();
            $ride->route->coordinates = $ride->getRoute()->getLinestring()->coordinates;
            echo json_encode($ride);
        }
    
        if (isset($_GET['segment-load'])) {
            $segment = new Segment($_GET['segment-load']);
            $segment->route->coordinates = $segment->route->getLinestring()->coordinates;
            echo json_encode($segment);
        }
    
        if (isset($_GET['segment-delete'])) {
            $segment = new Segment($_GET['segment-delete']);
            $segment->delete();
            echo json_encode(true);
        }

    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$var = json_decode($json, true);

if (is_array($var)) {
    
    if ($var['type'] == 'route-save' OR $var['type'] == 'route-edit') {
        $linestring = new CFLinestring($var['coordinates']);
        $var['startplace'] = new Geolocation($var['startplace']['city'], $var['startplace']['prefecture']);
        $var['goalplace'] = new Geolocation($var['goalplace']['city'], $var['goalplace']['prefecture']);
        if (empty($var['name'])) $var['name'] = 'My route';
        if (empty($var['description'])) $var['description'] = '';
        if (!isset($var['tunnels'])) $var['tunnels'] = [];
        if ($var['category'] == 'route' OR $var['type'] == 'route-edit') $linestring->createRoute(getConnectedUser()->id, $var['id'], 'route', $var['name'], $var['description'], $var['distance'], $var['elevation'], $var['startplace'], $var['goalplace'], $var['tunnels']);
        else if ($var['category'] == 'segment') $linestring->createSegment(getConnectedUser()->id, $var['id'], 'segment', $var['name'], $var['description'], $var['distance'], $var['elevation'], $var['startplace'], $var['goalplace'], $var['tunnels'], $var['rank'], $var['advised'], $var['seasons'], $var['advice'], $var['specs'], $var['tags']);
        echo json_encode($var);
    }

    if (isset($var['route-load'])) {
        $route = new Route($var['route-load']);
        echo json_encode($route);
    }

}