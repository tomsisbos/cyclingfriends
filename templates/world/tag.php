<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<link rel="stylesheet" href="/assets/css/mkpoint.css" />
<link rel="stylesheet" href="/assets/css/segment.css" />

<body> <?php 

    // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

        // Get id from URL
        $tag = new Tag(basename($_SERVER['REQUEST_URI']));
        if ($tag->exists()) {

            // Space for error messages
            displayMessage(); ?>

            <h2 class="top-title">Tag : <?= $tag->name ?></h2>

            <div class="container d-flex flex-column gap-20"> <?php

                // Define offset and number of articles to query
                $limit = 20;
                if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
                else $offset = 0;
                $entries = $tag->getEntries($offset, $limit);
                foreach ($entries as $entry) {
                    if ($entry->type == 'scenery') {
                        $mkpoint = $entry; ?>
                        <div class="top-link"><a href="/scenery/<?= $mkpoint->id ?>">Scenery spot</a></div> <?php
                        include '../includes/mkpoints/card.php';
                    } else if ($entry->type == 'segment') {
                        $segment = $entry;?>
                        <div class="top-link"><a href="/segment/<?= $segment->id ?>">Segment</a></div> <?php
                        include '../includes/segments/card.php';
                    }
                } ?>
            </div>

            <div class="container"> <?php
                // Set pagination system
                if (isset($_GET['p'])) $p = $_GET['p'];
                else $p = 1;
                $url = strtok($_SERVER["REQUEST_URI"], '?');
                $total_pages = count($entries) / $limit;
                
                // Build pagination menu
                include '../includes/pagination.php' ?>
            </div> <?php

        } ?>

    </div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>