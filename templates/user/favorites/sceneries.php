<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/scenery.css" />
<link rel="stylesheet" href="/assets/css/segment.css" />
<link rel="stylesheet" href="/assets/css/favorites.css" />

<body>

	<?php // Navbar
	include '../includes/navbar.php'; ?>

	<div class="main"> <?php

        // Space for general error messages
        include '../includes/result-message.php'; ?>

        <h2 class="top-title">絶景スポット</h2>

        <div class="container favorites"> <?php

            // Define offset and number of articles to query
            $limit = 20;
            if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
            else $offset = 0;
            $sceneries = $connected_user->getFavorites('scenery', $offset, $limit);
            foreach ($sceneries as $scenery) { ?>
                <div class="fav-card"> <?php
                    include '../includes/sceneries/card.php'; ?>
                    <div class="fav-card-appendice">
                        <div class="mp-button btn bg-darkred text-white js-favorite-button">除外</div>
                    </div>
                </div> <?php
            } ?>
        </div>

        <div class="container"> <?php
            // Set pagination system
            if (isset($_GET['p'])) $p = $_GET['p'];
            else $p = 1;
            $url = strtok($_SERVER["REQUEST_URI"], '?');
            $total_pages = ceil($connected_user->getFavoritesNumber('sceneries') / $limit);
            
            // Build pagination menu
            include '../includes/pagination.php' ?>
        </div>

    </div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>