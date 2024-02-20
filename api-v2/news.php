<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-public-head.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $limit = $_GET['limit'];
    $offset = $_GET['offset'];
    
    $getNews = $db->prepare("SELECT
        id,
        title, 
        content,
        type, 
        (CASE
            WHEN type = 'dev' THEN '開発'
            WHEN type = 'general' THEN '一般'
            ELSE '一般'
        END) as typestring,
        datetime::date as date
    FROM posts
    ORDER BY datetime DESC
    LIMIT {$limit}
    OFFSET {$offset}");
    $getNews->execute();
    $news = $getNews->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($news);

}