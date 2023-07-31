<?php

if (isset($_POST['comment']) AND !empty($_POST['comment'])) {
    $content = $_POST['content'];
    $object->postComment(getConnectedUser()->id, $content);
    $successmessage = "コメントが投稿されました！";
    if (getConnectedUser()->id != $object->user_id) $object->notify($object->user_id, 'activity_new_comment', getConnectedUser()->id);
    foreach ($object->getComments() as $comment) $object->notify($comment->user->id, 'activity_new_comment', getConnectedUser()->id);
}