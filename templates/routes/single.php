<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />

<?php 
session_start();
include '../actions/users/securityAction.php';
?>

<body>

	<?php include '../includes/navbar.php'; ?>

    <div class="main container-shrink"> <?php
    
        // Get id from URL
        $last_parameter = basename($_SERVER['REQUEST_URI']);
        if (is_numeric($last_parameter) || $last_parameter === 'route') {

            if (is_numeric($last_parameter)) $route = new Route($last_parameter);
            else {
                $url_fragments = explode('/', $_SERVER['REQUEST_URI']);
                $slug = array_slice($url_fragments, -2)[0];
                $ride = new Ride($slug);
                $route = $ride->route;
            } ?>
        
            <div class="container">
                <div class="header">
                    <h2 class="top-title"><?= $route->name ?></h2>
                    <div>by 
                        <a href="/rider/<?= $route->author->id ?>">
                            <strong><?= $route->author->login ?></strong>
                        </a>
                    </div>
                    <div class="header-buttons"> <?php
                        if ($ride) { ?>
                            <a href="/ride/<?= $ride->id ?>">
                                <button class="btn button" type="button">Back to ride page</button>
                            </a> <?php
                        }
                        if ($route->author->id == $connected_user->id) { ?>
                            <a href="/route/<?= $route->id ?>/edit">
                                <button class="btn button" type="button" name="edit">Edit</button>
                            </a>
                            <button class="btn button" id="deleteRoute" data-id="<?= $route->id ?>" type="button" name="delete">Delete</button> <?php
                        } ?>
                        <a id="export" download>
                            <button class="btn button" type="button">Export as *.gpx</button>
                        </a>
                    </div>
                </div>
            </div>
            <div class="container bg-white text-justify">
                <?= $route->description ?>
                <div class="specs">
                    <div class="spec-column">
                        <div><strong>Start : </strong><?= $route->startplace ?></div>
                        <div><strong>Goal : </strong><?= $route->goalplace ?></div>
                    </div>
                    <div class="spec-column">
                    <div><strong>Distance : </strong><?= round($route->distance, 1) ?>km</div>
                        <div><strong>Elevation : </strong><?= $route->elevation ?>m</div>
                    </div>
                    <div class="spec-column">
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
            <div class="container spec-table-container">
                <div class="spec-table">
                    <table id="routeTable">
                        <tbody>
                            <tr class="spec-table-th">
                                <th class="table-element e10 text-left">Distance</th>
                                <th class="table-element e40 text-left">Name</th>
                                <th class="table-element e20 text-center">Place</th>
                                <th class="table-element e15 text-center">Elevation</th>
                                <th class="table-element e25 text-center">Distance from route</th>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div> <?php
        } ?>
    </div>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/map/class/CFUtils.js"></script>
<script type="module" src="/scripts/routes/route.js"></script>
<script src="/scripts/routes/delete.js"></script>

</body>
</html>