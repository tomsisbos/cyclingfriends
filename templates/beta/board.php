<?php

include '../actions/users/initSessionAction.php';
include '../actions/beta/devNotesAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/beta.css">

<body>

	<?php include '../includes/navbar.php';
    
	// Define offset and number of articles to query
    $limit = 20;
    if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
    else $offset = 0; ?>

	<div class="main">

        <div class="container bg-admin">
            <h2>表示設定</h2> <?php
            include '../includes/beta/filter.php'; ?>
        </div>

        <div class="container bg-white end">
            <h2>開発ノート一覧</h2>
            <div class="dvnt-board-container"> <?php
                if (!empty($dev_notes)) foreach ($dev_notes as $dev_note) include '../includes/beta/devnote-card.php';
                else echo '<div class="error-block"><div class="error-message">表示するデータがありません。</div></div>' ?>
            </div>
        </div> <?php
        
        // Set pagination system
        if (isset($_GET['p'])) $p = $_GET['p'];
        else $p = 1;
        $url = strtok($_SERVER["REQUEST_URI"], '?');
        $total_dev_notes = $db->prepare('SELECT id FROM dev_notes');
        $total_dev_notes->execute();
        $total_pages = $total_dev_notes->rowCount() / $limit;
        
        // Build pagination menu
        include '../includes/pagination.php' ?>
	
	</div>
	
</body>
</html>