<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $activity = new Activity($data->activity_id);
    $content  = $data->content;
    
    // Add comment
    $activity->postComment($user->id, $content);

    // Notify activity author
    if ($user->id != $activity->user_id) $activity->notify($activity->user_id, 'activity_new_comment', $user->id);

    // Notify other comment posters
    foreach ($activity->getComments() as $comment) {
        if ($comment->user->id != $user->id) $activity->notify($comment->user->id, 'activity_new_comment', $user->id);
    }

    echo json_encode(true);
}