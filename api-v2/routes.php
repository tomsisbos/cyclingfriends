<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    $limit = $_GET['limit'];
    $offset = $_GET['offset'];

    // Get activity data
    $getRoutes = $db->prepare("SELECT
    SELECT
        r.id,
        r.author_id as user_id,
        r.posting_date,
        r.name,
        r.description,
        r.distance,
        r.elevation,
        r.startplace,
        r.goalplace,
        r.thumbnail_filename,
        r.privacy
    FROM
        routes r
    WHERE
        r.category = 'route' AND
        r.author_id = ?
    ORDER BY posting_date DESC
    LIMIT {$limit} OFFSET {$offset}");
    $getRoutes->execute([$user->id]);
    $routes = $getRoutes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($routes);
}