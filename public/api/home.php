<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/class/Autoloader.php'; 
Autoloader::register(); 
require_once $base_directory . '/includes/functions.php';
$connected_user = new User(11);
require $base_directory . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    /*if (isset($_GET['get-session'])) {
        if (isset($_SESSION['auth'])) echo json_encode($_SESSION);
    }*/

    if (isset($_GET['mkpoint-photos'])) {
        $mkpoint = new Mkpoint($_GET['mkpoint-photos']);
        echo json_encode($mkpoint->getImages());
    }

    if (isset($_GET['mkpoint-closest-photo'])) { // Get photo whose period is the soonest
        $getMkpointPhoto = $db->prepare('SELECT * FROM img_mkpoint WHERE mkpoint_id = ? AND month > ? ORDER BY month ASC');
        $getMkpointPhoto->execute([$_GET['mkpoint-closest-photo'], date('m')]);
        if ($getMkpointPhoto->rowCount() == 0) {
            $getMkpointPhoto = $db->prepare('SELECT * FROM img_mkpoint WHERE mkpoint_id = ? ORDER BY month DESC');
            $getMkpointPhoto->execute([$_GET['mkpoint-closest-photo']]);
        }
        $mkpointphoto = $getMkpointPhoto->fetch(PDO::FETCH_ASSOC);
        echo json_encode($mkpointphoto);
    }

    if (isset($_GET['getpropic'])) {
        if (is_numeric($_GET['getpropic'])) $user = new User($_GET['getpropic']);
        else $user = $connected_user;
        $profile_picture_src = $user->getPropicSrc();
        echo json_encode([$profile_picture_src]);
    }

    if (isset($_GET['display-mkpoints'])) {
        $mkpoints_number = 30;
        $getMkpoints = $db->prepare("SELECT id FROM map_mkpoint ORDER BY popularity DESC LIMIT 0, {$mkpoints_number}");
        $getMkpoints->execute();
        $result = $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
        $mkpoints = [];
        foreach ($result as $mkpoint_data) {
            $mkpoint = new Mkpoint($mkpoint_data['id']);
            if ($_GET['display-mkpoints'] == 'details') {
                if (isset($_SESSION['id'])) $mkpoint->isFavorite = $mkpoint->isFavorite();
                if (isset($_SESSION['id'])) $mkpoint->isCleared = $mkpoint->isCleared();
                $mkpoint->tags = $mkpoint->getTags();
            }
            array_push($mkpoints, $mkpoint);
        }
        echo json_encode($mkpoints);
    }

    if (isset($_GET['mkpoint'])) {
        $getMkpoint = $db->prepare('SELECT id FROM map_mkpoint WHERE id = ?');
        $getMkpoint->execute(array($_GET['mkpoint']));
        if ($getMkpoint->rowCount() > 0) {
            $mkpoint = new Mkpoint($_GET['mkpoint']);
            echo json_encode(['data' => $mkpoint, 'photos' => $mkpoint->getImages()]);
        } else echo json_encode(['error' => '該当する絶景スポットは存在していません。']);
    }

    if (isset($_GET['display-mkpoints-list'])) {
        $querystring = "SELECT * FROM map_mkpoint WHERE id IN (" .$_GET['display-mkpoints-list']. ")";
        $getMkpoints = $db->prepare($querystring);
        $getMkpoints->execute();
        $mkpointsList = $getMkpoints->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($mkpointsList);
    }

    if (isset($_GET['get-close-mkpoints'])) {
        $route = new Route($_GET['get-close-mkpoints']);
        $close_mkpoints = $route->getCloseMkpoints();
        echo json_encode($close_mkpoints);
    }

    if (isset($_GET['mkpoint-dragged'])) {
        if (isset($_GET['lng']) && isset($_GET['lat'])) {
            $mkpoint_id  = $_GET['mkpoint-dragged'];
            $mkpoint_lng = $_GET['lng'];
            $mkpoint_lat = $_GET['lat'];
            $updateMkpointLngLat = $db->prepare('UPDATE map_mkpoint SET lng = ?, lat = ? WHERE id = ?');
            $updateMkpointLngLat->execute(array($mkpoint_lng, $mkpoint_lat, $mkpoint_id));
            echo json_encode([$_GET['lng'], $_GET['lat']]);
        }
    }

    if (isset($_GET['get-rating'])) {
        if ($_GET['type'] == 'mkpoint') {
            $object = new Mkpoint($_GET['id']);
            $table = "map_mkpoint";
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
            $vote = $object->getUserVote($connected_user);
            $rating_infos['vote'] = $vote;
        }
        echo json_encode($rating_infos);
    }

    if (isset($_GET['display-segments'])) {
        $segments_number = 6;
        $getSegments = $db->prepare("SELECT id FROM segments ORDER BY popularity DESC LIMIT 0, {$segments_number}");
        $getSegments->execute();
        $segments = $getSegments->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($segments); $i++) {
            $segments[$i] = new Segment($segments[$i]['id'], false);
        }
        echo json_encode($segments);
    }

    if (isset($_GET['segment-mkpoints'])) {
        $segment = new Segment($_GET['segment-mkpoints']);
        $close_mkpoints = $segment->route->getCloseMkpoints(500);
        foreach ($close_mkpoints as $mkpoint) $mkpoint->photos = $mkpoint->getImages();
        echo json_encode($close_mkpoints);
    }

    if (isset($_GET['get-icon'])) {
        $filename = $_GET['get-icon'];
        $path = $_SERVER['DOCUMENT_ROOT']. "/map/media/" .$filename;
        echo json_encode($path);
    }

}