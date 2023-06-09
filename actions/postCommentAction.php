<?php

if (isset($_POST['comment']) AND !empty($_POST['comment'])) {
    $content = $_POST['content'];
    $object->postComment($connected_user->id, $content);
    $successmessage = "コメントが投稿されました！";
    if ($connected_user->id != $object->user_id) $object->notify($object->user_id, 'activity_new_comment', $connected_user->id);
}