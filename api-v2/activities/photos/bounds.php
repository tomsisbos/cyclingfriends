<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

// Bounds are expected to be set as an array like [Xlng, Xlat, Ylng, Ylat]
$month = $_GET['month'];
$getActivityPhotosFromBoundingBox = $db->prepare("SELECT
ap.id,
ap.user_id,
u.login as user_login,
ap.activity_id,
ap.elevation,
a.title,
ap.filename as uri,
ap.datetime,
ST_X(ap.point::geometry) as lng,
ST_Y(ap.point::geometry) as lat,
ROW_NUMBER() OVER (ORDER BY ABS(EXTRACT(MONTH FROM ap.datetime) - ?), ap.datetime DESC, RANDOM() ASC) as number
FROM activity_photos ap
JOIN users u ON ap.user_id = u.id
JOIN activities a ON ap.activity_id = a.id
WHERE ap.privacy = 'public' AND a.privacy = 'public' AND ST_Within(ap.point::geometry, ST_MakeEnvelope(?, ?, ?, ?, 4326))
ORDER BY ABS(EXTRACT(MONTH FROM ap.datetime) - ?), ap.datetime DESC, RANDOM() ASC
LIMIT ?");
$getActivityPhotosFromBoundingBox->execute([$_GET['bounds'][0], $_GET['bounds'][1], $_GET['bounds'][2], $_GET['bounds'][3], $_GET['month'], $_GET['limit']]);
require substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT']))) . '/actions/blobStorage.php';
$activity_photos = array_map(function ($activity_photo) use ($db, $blobClient) {
    $activity_photo['uri'] = $blobClient->getBlobUrl('activity-photos', $activity_photo['uri']);
    return $activity_photo;
}, $getActivityPhotosFromBoundingBox->fetchAll(PDO::FETCH_ASSOC));

echo json_encode($activity_photos);