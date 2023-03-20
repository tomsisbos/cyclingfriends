<?php

// Post news
if (!empty($_POST)) {
    $post = new Post();
    $post->create($_POST['title'], $_POST['type'], nl2br($_POST['content']));
    $successmessage = '「' .$post->title. '」が投稿されました！';
}