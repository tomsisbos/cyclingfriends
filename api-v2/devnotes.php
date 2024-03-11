<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $id = getNextAutoIncrement('dev_notes');
    $title = $data->title;
    $content = $data->content;
    $type = $data->type;
    $url = $data->url;
    $browser = $data->browser;
    $user_id = $user->id;
    $time = (new DateTime('now'))->setTimezone(new DateTimeZone('Asia/Tokyo'))->format('Y-m-d H:i:s');

    $registerDevNote = $db->prepare("INSERT INTO dev_notes (user_id, time, type, title, content, url, browser) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $registerDevNote->execute(array($user_id, $time, $type, $title, $content, $url, $browser));
    $dev_note = new DevNote($id);

    // Notify administrators
    $getAdmins = $db->prepare("SELECT id FROM users WHERE rights = 'administrator'");
    $getAdmins->execute();
    $ids = $getAdmins->fetchAll(PDO::FETCH_COLUMN);
    foreach ($ids as $id) $dev_note->notify($id, 'new_devnote');

    // Fetch notes
    $getDevNotes = $db->prepare("SELECT
        dn.id,
        dn.content,
        dn.user_id,
        u.login as author_login,
        u.default_profilepicture_id as default_propic_id,
        pp.filename as author_propic,
        dn.time,
        dn.type,
        dn.title,
        dn.content,
        dn.url,
        dn.browser
    FROM dev_notes dn
    JOIN users u ON dn.user_id = u.id
    JOIN profile_pictures pp ON dn.user_id = pp.user_id
    WHERE dn.type = ?
    ORDER BY dn.time DESC");
    $getDevNotes->execute([$type]);
    $devnotes = $getDevNotes->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($devnotes);

}