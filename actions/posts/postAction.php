<?php

if (!empty($_POST)) {
    
    // Post news
    $post = new Post();
    $result = $post->create($_POST['title'], $_POST['type'], nl2br($_POST['content']));

    // Post a tweet on admin account
    $twitter = (new User(2))->getTwitter();
    $nl = chr(13) . chr(10);
    $tweet_head = '【' .$_POST['title']. '】' . $nl . $nl;
    $tweet_foot = $nl . $nl .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/news';
    if (strlen($tweet_head . $_POST['content']) < 135) $content = $tweet_head . $_POST['content'] . $tweet_foot;
    else $content = mb_substr($tweet_head . $_POST['content'], 0, 134). '...' .$tweet_foot;
    $response = $twitter->post($content);
    var_dump($response);

    if ($result) $successmessage = '「' .$post->title. '」が投稿されました！';
}