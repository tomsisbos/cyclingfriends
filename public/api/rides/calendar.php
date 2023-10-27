<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

if (isset($_GET['task'])) {

    if ($_GET['task'] == 'rides') {

        $getOfficialRides = $db->prepare("SELECT id FROM rides WHERE privacy = 'public' AND author_id IN (SELECT id FROM users WHERE rights = 'administrator') ORDER BY date > NOW() DESC, CASE WHEN date > NOW() THEN (SELECT cast(extract(epoch FROM date) AS integer)) ELSE -(SELECT cast(extract(epoch FROM date) AS integer)) END ASC LIMIT {$_GET['limit']} OFFSET {$_GET['offset']}");
        $getOfficialRides->execute();

        $rides = array_map(function ($id) {
            $ride = new Ride($id);
            $ride->status = $ride->getStatus()['status'];
            $ride->status_class = $ride->getStatusClass();
            $ride->map_thumbnail = $ride->getMapThumbnail();
            if ($ride->hasFeaturedImage()) $ride->featured_image = $ride->getFeaturedImage()->url;
            return $ride;
        }, $getOfficialRides->fetchAll(PDO::FETCH_COLUMN));

        echo json_encode($rides);

    }

}