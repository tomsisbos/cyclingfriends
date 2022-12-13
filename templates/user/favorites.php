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
            $segments = $connected_user->getFavorites('segment');
            foreach ($segments as $segment) { ?>
                <div class="fav-card"> <?php
                    include '../includes/segments/card.php'; ?>
                    <div class="fav-card-appendice">
                        <div class="mp-button btn bg-darkred text-white js-favorite-button">Remove from favorites</div>
                    </div>
                </div> <?php
            } ?>
        </div>

        <h2 class="top-title">Sceneries</h2>

        <div class="container favorites"> <?php
            $mkpoints = $connected_user->getFavorites('scenery');
            foreach ($mkpoints as $mkpoint) { ?>
                <div class="fav-card"> <?php
                    include '../includes/mkpoints/card.php'; ?>
                    <div class="fav-card-appendice">
                        <div class="mp-button btn bg-darkred text-white js-favorite-button">Remove from favorites</div>
                    </div>
                </div> <?php
            } ?>
        </div>

    </div>

</body>
</html>

<script src="/scripts/user/favorites.js"></script>