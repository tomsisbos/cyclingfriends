<?php

header('Content-Type: application/json, charset=UTF-8');

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/actions/users/initPublicSession.php';
require_once $base_directory . '/includes/functions.php';
require_once $base_directory . '/actions/database.php';

$getPhotos = $db->prepare("SELECT
    id,
    filename, 
    EXTRACT(EPOCH FROM datetime) as datetime, 
    featured,
    elevation,
    privacy, 
    ST_X(point::geometry)::double precision as lng, 
    ST_Y(point::geometry)::double precision as lat 
FROM activity_photos 
WHERE activity_id = ? 
ORDER BY featured::int DESC, RANDOM()");
$getPhotos->execute([$_GET['id']]);
echo json_encode($getPhotos->fetchAll(PDO::FETCH_ASSOC));