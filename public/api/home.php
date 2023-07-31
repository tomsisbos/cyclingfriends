<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
include_once $base_directory . "/vendor/phayes/geophp/geoPHP.inc";
require_once $base_directory . '/includes/functions.php';
$connected_user = new User(11);
require $base_directory . '/actions/database.php';

// In case an Ajax request have been detected
if (isAjax()) {

    /*if (isset($_GET['get-session'])) {
        if (isSessionActive()) echo json_encode($_SESSION);
    }*/

    if (isset($_GET['scenery-photos'])) {
        $scenery = new Scenery($_GET['scenery-photos']);
        echo json_encode($scenery->getImages());
    }

    if (isset($_GET['scenery-closest-photo'])) { // Get photo whose period is the soonest
        $getSceneryPhoto = $db->prepare('SELECT * FROM scenery_photos WHERE scenery_id = ? AND month > ? ORDER BY month ASC');
        $getSceneryPhoto->execute([$_GET['scenery-closest-photo'], date('m')]);
        if ($getSceneryPhoto->rowCount() == 0) {
            $getSceneryPhoto = $db->prepare('SELECT * FROM scenery_photos WHERE scenery_id = ? ORDER BY month DESC');
            $getSceneryPhoto->execute([$_GET['scenery-closest-photo']]);
        }
        $sceneryphoto = $getSceneryPhoto->fetch(PDO::FETCH_ASSOC);
        echo json_encode($sceneryphoto);
    }

    if (isset($_GET['getpropic'])) {
        if (is_numeric($_GET['getpropic'])) $user = new User($_GET['getpropic']);
        else $user = getConnectedUser();
        $profile_picture_src = $user->getPropicUrl();
        echo json_encode([$profile_picture_src]);
    }

    if (isset($_GET['display-sceneries'])) {
        $sceneries_number = 30;
        $getSceneries = $db->prepare("SELECT id, user_id, name, thumbnail, ST_X(point) as lng, ST_Y(point) as lat, rating, grades_number, popularity FROM sceneries ORDER BY popularity DESC LIMIT 0, {$sceneries_number}");
        $getSceneries->execute();
        $sceneries = $getSceneries->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sceneries);
    }

    if (isset($_GET['scenery'])) {
        $getScenery = $db->prepare('SELECT id FROM sceneries WHERE id = ?');
        $getScenery->execute(array($_GET['scenery']));
        if ($getScenery->rowCount() > 0) {
            $scenery = new Scenery($_GET['scenery']);
            echo json_encode(['data' => $scenery, 'photos' => $scenery->getImages()]);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
    }

    if (isset($_GET['display-sceneries-list'])) {
        $querystring = "SELECT * FROM sceneries WHERE id IN (" .$_GET['display-sceneries-list']. ")";
        $getSceneries = $db->prepare($querystring);
        $getSceneries->execute();
        $sceneriesList = $getSceneries->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($sceneriesList);
    }

    if (isset($_GET['get-close-sceneries'])) {
        $route = new Route($_GET['get-close-sceneries']);
        $close_sceneries = $route->getLinestring()->getCloseSceneries();
        echo json_encode($close_sceneries);
    }

    if (isset($_GET['scenery-dragged'])) {
        if (isset($_GET['lng']) && isset($_GET['lat'])) {
            $scenery_id  = $_GET['scenery-dragged'];
            $scenery_lng = $_GET['lng'];
            $scenery_lat = $_GET['lat'];
            $updateSceneryLngLat = $db->prepare('UPDATE sceneries SET lng = ?, lat = ? WHERE id = ?');
            $updateSceneryLngLat->execute(array($scenery_lng, $scenery_lat, $scenery_id));
            echo json_encode([$_GET['lng'], $_GET['lat']]);
        }
    }

    if (isset($_GET['scenery-details'])) {
        $scenery_id = $_GET['scenery-details'];
        $getScenery = $db->prepare('SELECT id FROM sceneries WHERE id = ?');
        $getScenery->execute(array($scenery_id));
        if ($getScenery->rowCount() > 0) {
            $scenery = new Scenery($scenery_id);
            if (isset($_SESSION['id'])) $scenery->isFavorite = $scenery->isFavorite();
            if (isset($_SESSION['id'])) $scenery->isCleared = $scenery->isCleared();
            $scenery->tags = $scenery->getTags();
            $scenery->photos = $scenery->getImages();
            echo json_encode($scenery);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
    }

    if (isset($_GET['get-rating'])) {
        if ($_GET['type'] == 'scenery') {
            $object = new Scenery($_GET['id']);
            $table = "sceneries";
        } else if ($_GET['type'] == 'segment') {
            $object = new Segment($_GET['id']);
            $table = "segments";
        }
        // Get rating info
        $checkRating = $db->prepare("SELECT rating, grades_number FROM {$table} WHERE id = ?");
        $checkRating->execute(array($object->id));
        $rating_infos = $checkRating->fetch(PDO::FETCH_ASSOC);
        // Add user vote info
        if (isset($_SESSION['id'])) {
            $vote = $object->getUserVote(getConnectedUser());
            $rating_infos['vote'] = $vote;
        }
        echo json_encode($rating_infos);
    }

    if (isset($_GET['display-segments'])) {
        $getSegments = $db->prepare('SELECT id, route_id, rank, name, advised, popularity FROM segments ORDER BY popularity DESC');
        $getSegments->execute();
        $segments = $getSegments->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($segments); $i++) {
            // Add coordinates
            $getCoords = $db->prepare('SELECT ST_AsWKT(linestring) FROM linestrings WHERE segment_id = ?');
            $getCoords->execute(array($segments[$i]['route_id']));
            $linestring_wkt = $getCoords->fetch(PDO::FETCH_COLUMN);
            $coordinates = new CFLinestring();
            $coordinates->fromWKT($linestring_wkt);
            $segments[$i]['coordinates'] = $coordinates->getArray();
            // Add tunnels
            $getLinestring = $db->prepare('SELECT ST_AsWKT(linestring) FROM tunnels WHERE segment_id = ?');
            $getLinestring->execute(array($segments[$i]['route_id']));
            $tunnels = [];
            while ($linestring_wkt = $getLinestring->fetch(PDO::FETCH_COLUMN)) {
                $tunnel = new Tunnel();
                $tunnel->fromWKT($linestring_wkt);
                array_push($tunnels, $tunnel->getArray());
            }
            $segments[$i]['tunnels'] = $tunnels;
            // Add tags
            $getTags = $db->prepare('SELECT tag FROM tags WHERE object_type = ? AND object_id = ?');
            $getTags->execute(['segment', $segments[$i]['id']]);
            $tags = $getTags->fetchAll(PDO::FETCH_COLUMN);
            $segments[$i]['tags'] = $tags;
        }
        echo json_encode($segments);
    }    
    
    if (isset($_GET['segment-details'])) {
        $segment_id = $_GET['segment-details'];
        echo json_encode(new Segment($segment_id, false));
    }

    if (isset($_GET['segment-sceneries'])) {
        $segment = new Segment($_GET['segment-sceneries']);
        $close_sceneries = $segment->route->getLinestring()->getCloseSceneries(500);
        foreach ($close_sceneries as $scenery) $scenery->photos = $scenery->getImages();
        echo json_encode($close_sceneries);
    }

    if (isset($_GET['get-icon'])) {
        $filename = $_GET['get-icon'];
        $path = $_SERVER['DOCUMENT_ROOT']. "/map/media/" .$filename;
        echo json_encode($path);
    }

}