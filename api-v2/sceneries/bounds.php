<?php

header('Content-Type: application/json, charset=UTF-8');

require_once '../includes/api-public-head.php';

// Bounds are expected to be set as an array like [Xlng, Xlat, Ylng, Ylat]
$getSceneriesFromBoundingBox = $db->prepare("WITH scenery_grades_counts AS (
    SELECT
        scenery_id,
        COUNT(grade) as grades_number
    FROM
        scenery_grades
    GROUP BY
        scenery_id
)

SELECT
    s.id,
    s.user_id,
    u.default_profilepicture_id as user_default_propic_id,
    pp.filename as user_propic,
    s.name,
    s.description,
    s.city,
    s.prefecture,
    s.elevation,
    AVG(g.grade) as rating,
    COALESCE(c.grades_number, 0) as grades_number,
    s.date,
    ST_X(s.point::geometry) as lng,
    ST_Y(s.point::geometry) as lat,
    COALESCE(array_to_string(array_agg(DISTINCT p.id), ','), '') as photo_ids,
    COALESCE(array_to_string(array_agg(DISTINCT t.tag), ','), '') as tags
FROM
    sceneries AS s
JOIN
    scenery_photos AS p ON s.id = p.scenery_id
LEFT JOIN
    tags AS t ON t.object_type = 'scenery' AND t.object_id = s.id
INNER JOIN
    users AS u ON s.user_id = u.id
INNER JOIN
    profile_pictures AS pp ON s.user_id = pp.user_id
LEFT JOIN
    scenery_grades AS g ON s.id = g.scenery_id
LEFT JOIN
    scenery_grades_counts AS c ON s.id = c.scenery_id
WHERE
    ST_Within(s.point::geometry, ST_MakeEnvelope(?, ?, ?, ?, 4326))
GROUP BY
    s.id, s.user_id, s.name, s.description, s.city, s.prefecture, s.elevation, s.popularity, s.date, lng, lat, user_default_propic_id, user_propic, c.grades_number

ORDER BY
    AVG(g.grade), COALESCE(c.grades_number, 0) DESC, RANDOM()
LIMIT ?");
$getSceneriesFromBoundingBox->execute([$_GET['bounds'][0], $_GET['bounds'][1], $_GET['bounds'][2], $_GET['bounds'][3], $_GET['limit']]);
$sceneries = array_map(function ($scenery) use ($db) {
    require substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT']))) . '/actions/blobStorage.php';
    // Generate all photo instances and store them in the 'photos' array property
    $scenery['photos'] = [];
    $scenery['photo_ids'] = explode(',', $scenery['photo_ids']);
    $scenery['photos'] = array_map(function ($id) use ($db, $blobClient) {
        $query = $db->prepare("SELECT p.filename as uri, p.date, u.login FROM scenery_photos AS p JOIN users AS u ON p.user_id = u.id WHERE p.id = ?");
        $query->execute([$id]);
        $result = $query->fetch(PDO::FETCH_ASSOC);
        $result['uri'] = $blobClient->getBlobUrl('scenery-photos', $result['uri']);
        return $result;
    }, $scenery['photo_ids']); 
    unset($scenery['photo_ids']);
    // Format tags array
    if (!empty($scenery['tags'])) $scenery['tags'] = explode(',', $scenery['tags']);
    else $scenery['tags'] = array(); // Set tags to an empty array if there are no tags
    return $scenery;
}, $getSceneriesFromBoundingBox->fetchAll(PDO::FETCH_ASSOC));

echo json_encode($sceneries);