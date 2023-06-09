<?php
    
// Define offset and number of articles to query
$limit = 20;
if (isset($_GET['p'])) $offset = (intval($_GET['p']) - 1) * $limit;
else $offset = 0;

include '../actions/users/initSessionAction.php';
include '../actions/dev/devNotesAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/beta.css">

<body> <?php

    include '../includes/navbar.php'; ?>

	<div class="main">

        <div class="container bg-admin">
            <h2>表示設定</h2> <?php
            include '../includes/dev/filter.php'; ?>
        </div>

        <div class="container bg-white end">
            <h2>開発ノート一覧</h2>
            <div class="dvnt-board-container"> <?php
                if (!empty($dev_notes)) foreach ($dev_notes as $dev_note) include '../includes/dev/devnote-card.php';
                else echo '<div class="error-block"><div class="error-message">表示するデータがありません。</div></div>' ?>
            </div>
        </div> <?php
        
        // Set pagination system
        if (isset($_GET['p'])) $p = $_GET['p'];
        else $p = 1;
        $url = strtok($_SERVER["REQUEST_URI"], '?');
        $total_dev_notes = $db->prepare($query);
        $total_dev_notes->execute($params);
        $total_pages = ceil($total_dev_notes->rowCount() / $limit);
        
        // Build pagination menu
        include '../includes/pagination.php' ?>
	
	</div>
	
</body>
</html>