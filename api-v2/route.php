<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    
    $json = file_get_contents('php://input');
    $data = json_decode($json);
    
    $route = new Route($data->id);
    if ($user->id == $route->author->id) {
        $route->delete();
        echo json_encode(['success' => "ルートが削除されました。"]);
    }
    else echo json_encode(['error' => "このルートを削除する権限がありません。"]);

}