<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/scenery.css" />
<link rel="stylesheet" href="/assets/css/segment.css" />

<body> <?php 

    // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

        // Get id from URL
        $tag = new Tag(basename($_SERVER['HTTP_REFERER']));
        if ($tag->exists()) {

            // Space for general error messages
            include '../includes/result-message.php'; ?>

            <h2 class="top-title">Tag : <?= $tag->getString() ?></h2>

            <div class="container d-flex flex-column gap-20"> <?php

                // Define offset and number of articles to query
                $limit = 20;
                if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
                else $offset = 0;
                $entries = $tag->getEntries($offset, $limit);
                foreach ($entries as $entry) {
                    if ($entry->type == 'scenery') {
                        $scenery = $entry; ?>
                        <div class="top-link"><a href="/scenery/<?= $scenery->id ?>">絶景スポット</a></div> <?php
                        include '../includes/sceneries/card.php';
                    } else if ($entry->type == 'segment') {
                        $segment = $entry;?>
                        <div class="top-link"><a href="/segment/<?= $segment->id ?>">セグメント</a></div> <?php
                        include '../includes/segments/card.php';
                    }
                } ?>
            </div>

            <div class="container"> <?php
                // Set pagination system
                if (isset($_GET['p'])) $p = $_GET['p'];
                else $p = 1;
                $url = strtok($_SERVER["REQUEST_URI"], '?');
                $total_pages = $tag->getEntriesNumber() / $limit;
                
                // Build pagination menu
                include '../includes/pagination.php' ?>
            </div> <?php

        } ?>

    </div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>