<?php

if (isset($_POST['comment']) AND !empty($_POST['comment'])) {

    // Post comment
    $content = $_POST['content'];
    $object->postComment(getConnectedUser()->id, $content);
    $successmessage = "コメントが投稿されました！";

    // Notify object author
    if (getConnectedUser()->id != $object->user_id) $object->notify($object->user_id, 'activity_new_comment', getConnectedUser()->id);

    // Notify other comment posters
    foreach ($object->getComments() as $comment) {
        if ($comment->user->id != getConnectedUser()->id) $object->notify($comment->user->id, 'activity_new_comment', getConnectedUser()->id);
    }
}