<?php

require '../../includes/api-head.php';
    
if (isset($_GET)) {
        
    if ($_GET['task'] == 'rides') {

        $limit = $_GET['number'];

        $getRides = $db->prepare("
            SELECT DISTINCT
                r.id, r.name, r.date, r.description, c.filename as featured_image
            FROM rides as r
            JOIN ride_checkpoints as c
            ON r.id = c.ride_id AND c.featured = 1
            WHERE
                r.author_id = 2 AND
                r.privacy = 'public' AND
                (r.entry_start < NOW() AND r.entry_end > NOW())
            ORDER BY r.date ASC
            LIMIT 3
        ");
        $getRides->execute();
        $rides = $getRides->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($rides);

    } else if ($_GET['task'] == 'news') {

        $getNews = $db->prepare("
            SELECT id, title, content, type, 
                (CASE
                    WHEN type = 'dev' THEN '開発'
                    WHEN type = 'general' THEN '一般'
                    ELSE '一般'
                END) as typeString,
            datetime::date as date
            FROM posts
            ORDER BY datetime DESC
            LIMIT 1
        ");
        $getNews->execute();
        $news = $getNews->fetch(PDO::FETCH_ASSOC);

        echo json_encode($news);

    }
}