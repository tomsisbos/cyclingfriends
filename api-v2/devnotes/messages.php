<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

$get_messages_query = "SELECT
dc.id,
dc.number,
dc.content,
dc.user_id,
u.login as author_login,
u.default_profilepicture_id as default_propic_id,
pp.filename as author_propic
FROM dev_chat dc
JOIN users u ON dc.user_id = u.id
JOIN profile_pictures pp ON dc.user_id = pp.user_id
WHERE dc.note_id = ?
ORDER BY dc.number ASC";

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $id = $_GET['id'];

    $getDevChat = $db->prepare($get_messages_query);
    $getDevChat->execute([$id]);
    $devmessages = $getDevChat->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($devmessages);
}

else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $note_id = $data->note_id;
    $content = $data->content;
    $user_id = $user->id;

    $note = new DevNote($note_id);
    $note->post($content, $user_id);

    $getDevChat = $db->prepare($get_messages_query);
    $getDevChat->execute([$note_id]);
    $devmessages = $getDevChat->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($devmessages);

}