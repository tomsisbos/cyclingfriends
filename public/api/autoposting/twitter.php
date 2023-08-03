<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php'; 
CFAutoloader::register();
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/database.php';

// Function
$getSceneryToPost = $db->prepare("SELECT id FROM autoposting WHERE entry_type = 'scenery' ORDER BY id DESC");
$getSceneryToPost->execute();
$id = $getSceneryToPost->fetch(PDO::FETCH_COLUMN);
$entry = new AutopostingEntry();
$entry->populate($id);
$result = $entry->post();
if (isset($result->data)) $entry->remove();

echo json_encode($result);