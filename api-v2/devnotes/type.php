<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $type = $_GET['type'];
    $offset = $_GET['offset'];
    $limit = $_GET['limit'];

    $getDevNotes = $db->prepare("SELECT
        dn.id,
        dn.content,
        dn.user_id,
        u.login as author_login,
        u.default_profilepicture_id as default_propic_id,
        pp.filename as author_propic,
        dn.time,
        dn.type,
        dn.title,
        dn.content,
        dn.url,
        dn.browser
    FROM dev_notes dn
    JOIN users u ON dn.user_id = u.id
    JOIN profile_pictures pp ON dn.user_id = pp.user_id
    WHERE dn.type = ?
    ORDER BY dn.time DESC
    LIMIT {$limit} OFFSET {$offset}");
    $getDevNotes->execute([$type]);
    $devnotes = $getDevNotes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($devnotes);
}