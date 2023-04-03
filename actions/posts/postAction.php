<?php

// Post news
if (!empty($_POST)) {
    $post = new Post();
    $result = $post->create($_POST['title'], $_POST['type'], nl2br($_POST['content']));
    if ($result) $successmessage = '「' .$post->title. '」が投稿されました！';
}