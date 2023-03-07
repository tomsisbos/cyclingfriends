<?php

require '../../includes/api-head.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {
    $title = htmlspecialchars($data['title']);
    $content = nl2br(htmlspecialchars($data['content']));
    $registerDevNote = $db->prepare("INSERT INTO dev_notes (user_id, time, type, title, content, url, browser) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $registerDevNote->execute(array($connected_user->id, date('Y-m-d H:i:s'), $data['type'], $title, $content, $data['url'], $data['browser']));
    echo json_encode(['success' => 'ご協力頂き、ありがとうございます！新規の開発ノートが作成されました。<a href="/beta/board" target="_blank">ベータテスト管理パネル</a>に表示されます。']);

} ?>