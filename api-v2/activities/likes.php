<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    require_once $base_directory . '/includes/api-authentication.php';

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $activity = new Activity($data->id);
    $activity->toggleLike($user->id);

    echo json_encode($activity->getLikes());

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    
    require_once '../includes/api-public-head.php';

    $activity_id = $_GET['id'];
    
    $getLikes = $db->prepare("SELECT
        u.id as user_id,
        u.login,
        u.default_profilepicture_id as default_propic_id, 
        pp.filename as propic
    FROM activity_likes al
    JOIN users u ON u.id = al.user_id
    JOIN profile_pictures pp ON pp.user_id = u.id
    WHERE al.activity_id = ?");
    $getLikes->execute([$activity_id]);
    $likes = $getLikes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($likes);

}