<?php

require '../../includes/api-head.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {
    $title = htmlspecialchars($data['title']);
    $content = nl2br(htmlspecialchars($data['content']));
    $id = getNextAutoIncrement('dev_notes');
    $registerDevNote = $db->prepare("INSERT INTO dev_notes (user_id, time, type, title, content, url, browser) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $registerDevNote->execute(array($connected_user->id, date('Y-m-d H:i:s'), $data['type'], $title, $content, $data['url'], $data['browser']));
    $dev_note = new DevNote($id);

    // Notify administrators
    $getAdmins = $db->prepare("SELECT id FROM users WHERE rights = 'administrator'");
    $getAdmins->execute();
    $ids = $getAdmins->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) $dev_note->notify($id, 'new_devnote');

    echo json_encode(['success' => 'ご協力頂き、ありがとうございます！新規の開発ノートが作成されました。<a href="/dev/board" target="_blank">ベータテスト管理パネル</a>に表示されます。']);

} ?>