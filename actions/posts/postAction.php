<?php

// Post news
if (!empty($_POST)) {
    $post = new Post();
    $post->create($_POST['title'], $_POST['type'], $_POST['content']);
    $successmessage = '「' .$post->title. '」が投稿されました！';
}