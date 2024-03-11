<?php

// Get id from URL
$last_parameter = basename($_SERVER['REQUEST_URI']);
if (is_numeric($last_parameter)) {

    $slug = intval($last_parameter);
    $dev_note = new DevNote($slug);

} else header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST']. '/dev/board');

// Post new message
if (!empty($_POST) && isset($_POST['message'])) {
    $dev_note->post(nl2br(htmlspecialchars($_POST['message'])), $_SESSION['id']);
    $successmessage = 'メッセージが追加されました！';
    // Reset post variable
    $_POST = [];
}