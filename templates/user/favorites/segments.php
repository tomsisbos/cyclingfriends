<!DOCTYPE html>
<html lang="en">

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<link rel="stylesheet" href="/assets/css/mkpoint.css" />
<link rel="stylesheet" href="/assets/css/segment.css" />
<link rel="stylesheet" href="/assets/css/favorites.css" />

<body>

	<?php // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

		// Space for error messages
		displayMessage(); ?>

        <h2 class="top-title">Segments</h2>

        <div class="container favorites"> <?php
        
            // Define offset and number of articles to query
            $limit = 20;
            if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
            else $offset = 0;
            $segments = $connected_user->getFavorites('segment', $offset, $limit);
            foreach ($segments as $segment) { ?>
                <div class="fav-card"> <?php
                    include '../includes/segments/card.php'; ?>
                    <div class="fav-card-appendice">
                        <div class="mp-button btn bg-darkred text-white js-favorite-button">Remove from favorites</div>
                    </div>
                </div> <?php
            } ?>
        </div>

        <div class="container"> <?php
            // Set pagination system
            if (isset($_GET['p'])) $p = $_GET['p'];
            else $p = 1;
            $url = strtok($_SERVER["REQUEST_URI"], '?');
            $total_pages = $connected_user->getFavoritesNumber('segments') / $limit;
            
            // Build pagination menu
            include '../includes/pagination.php' ?>
        </div>

    </div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>