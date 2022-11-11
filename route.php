<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />

<?php 
session_start();
include 'includes/head.php';
include 'actions/users/securityAction.php';
?>

<body>

	<?php include 'includes/navbar.php'; ?>

    <div class="main"> <?php
    
        // Get id from URL
        if (isset($_GET['id'])) {
            
            $route = new Route ($_GET['id']); ?>
        
            <div class="container">
                <div class="d-flex gap align-items-center">
                    <h2 class="top-title"><?= $route->name ?></h2>
                    <div>by 
                        <a href="/riders/profile.php?id=<?= $route->author->id ?>">
                            <strong><?= $route->author->login ?></strong>
                        </a>
                    </div>
                        <div class="d-flex gap push"> <?php
                            if ($route->author == $connected_user) { ?>
                                <a href="/routes/edit.php?id=<?= $route->id ?>">
                                    <button class="btn button" type="button" name="edit">Edit</button>
                                </a>
                                <button class="btn button" id="deleteRoute" type="button" name="delete">Delete</button> <?php
                            } ?>
                            <a id="export" download>
                                <button class="btn button" type="button">Export as *.gpx</button>
                            </a>
                    </div>
                </div>
            </div>
            <div class="container bg-white">
                <?= $route->description ?>
                <div class="d-flex gap-20">
                    <div class="rt-specs d-flex flex-column col-4">
                        <div><strong>Start : </strong><?= $route->startplace ?></div>
                        <div><strong>Goal : </strong><?= $route->goalplace ?></div>
                    </div>
                    <div class="rt-specs d-flex flex-column col-4">
                    <div><strong>Distance : </strong><?= round($route->distance, 1) ?>km</div>
                        <div><strong>Elevation : </strong><?= $route->elevation ?>m</div>
                    </div>
                    <div class="rt-specs d-flex flex-column col-4">
                        <div><strong>Estimated time : </strong><?= $route->calculateEstimatedTime($connected_user->level)->format('H:i') ?></div>
                        <div><strong>Difficulty : </strong><?= $route->getStars($route->calculateDifficulty()) ?></div>
                    </div>
                </div>
                <div class="rt-slider"></div>
            </div>
            <div id="routePageMapContainer">
                <div id="routePageMap"></div>
                <div class="grabber"></div>
            </div>
            <div id="profileBox" class="container p-0" style="height: 20vh; background-color: white;">
                <canvas id="elevationProfile"></canvas>
            </div>
            <div class="container">
                <div class="spec-table">
                    <table id="routeTable">
                        <tbody>
                            <tr class="spec-table-th">
                                <th class="element-10 text-left">Distance</th>
                                <th class="element-40 text-left">Name</th>
                                <th class="element-20 text-center">Place</th>
                                <th class="element-15 text-center">Elevation</th>
                                <th class="element-25 text-center">Distance from route</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> <?php
        } ?>
    </div>

<script src="/map/vendor.js"></script>
<script type="module" src="/map/class/CFUtils.js"></script>
<script type="module" src="/routes/routePageMap.js"></script>

</body>
</html>