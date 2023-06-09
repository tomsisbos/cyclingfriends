<?php

if (isset($_POST['comment']) AND !empty($_POST['comment'])) {
    $content = $_POST['content'];
    $object->postComment($connected_user->id, $content);
    $successmessage = "コメントが投稿されました！";
    $object->notify($object->user_id, 'activity_new_comment', $connected_user->id);
}