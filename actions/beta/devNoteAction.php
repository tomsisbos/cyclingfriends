<?php

// Get id from URL
$last_parameter = basename($_SERVER['REQUEST_URI']);
if (is_numeric($last_parameter)) {

    $slug = intval($last_parameter);
    $dev_note = new DevNote($slug);

} else header('location: /beta/board');

// Post new message
if (!empty($_POST) && isset($_POST['message'])) {
    require '../actions/databaseAction.php';
    $postDevChatMessage = $db->prepare("INSERT INTO dev_chat (note_id, number, user_id, content) VALUES (?, ?, ?, ?)");
    $postDevChatMessage->execute(array($slug, count($dev_note->chat) + 1, $connected_user->id, $_POST['message']));
    $successmessage = 'メッセージが追加されました！';
    // Reset post variable
    $_POST = [];
    // Update current instance
    $dev_note = new DevNote($slug);
}