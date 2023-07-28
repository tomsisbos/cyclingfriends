<?php

include '../actions/users/initPublicSessionAction.php';
include '../includes/head.php';
include '../actions/routes/routeAction.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />

<body class="relative-navbar">

	<?php include '../includes/navbar.php'; ?>

    <div class="container-shrink"> <?php
        
        if (isset($ride)) $header_background_img = $ride->getFeaturedImage();
        else if ($route->getFeaturedImage()) $header_background_img = $route->getFeaturedImage();
        if (isset($header_background_img)) { ?>
            <div class="container header" style="background-image: <?= 'url(' .$header_background_img->url. '); background-size: cover;' ?>"> <?php
        } else { ?>
            <div class="container bg-grey"> <?php
        } ?>
            <div class="header-block">
                <div class="header-row">
                    <h2><?= $route->name ?></h2>
                </div>
                <div class="header-row">
                    <a href="/rider/<?= $route->author->id ?>"><?php $route->author->getPropicElement(30, 30, 30); ?></a>
                    <p>by <strong><?= $route->author->login ?></strong></p>
                </div>
                <div class="header-row mt-2"> <?php
                    if (isset($ride)) { ?>
                        <a href="/ride/<?= $ride->id ?>">
                            <button class="mp-button normal" type="button">ライドページに戻る</button>
                        </a> <?php
                    }

                    include '../includes/routes/export-button.php';

                    if (isSessionActive() && $route->author->id == getConnectedUser()->id) { ?>
                        <a href="/route/<?= $route->id ?>/edit">
                            <button class="mp-button success" type="button" name="edit">編集</button>
                        </a>
                        <button class="mp-button danger" id="deleteRoute" data-id="<?= $route->id ?>" type="button" name="delete">削除</button> <?php
                    } ?>
                </div>
            </div>
        </div>
        <div class="container bg-white text-justify">
            <?= $route->description ?>
            <div class="specs">
                <div class="spec-column">
                    <div><strong>集合場所 : </strong><?= $route->startplace->toString() ?></div>
                    <div><strong>解散場所 : </strong><?= $route->goalplace->toString() ?></div>
                </div>
                <div class="spec-column">
                <div><strong>距離 : </strong><?= round($route->distance, 1) ?>km</div>
                    <div><strong>獲得標高 : </strong><?= $route->elevation ?>m</div>
                </div>
                <div class="spec-column">
                    <div><strong>予測時間 : </strong><?php
                        if (isSessionActive()) echo $route->calculateEstimatedTime(getConnectedUser()->level)->format('H:i');
                        else echo $route->calculateEstimatedTime(1)->format('H:i') ?>
                    </div>
                    <div><strong>難易度 : </strong><?= $route->getStars($route->calculateDifficulty()) ?></div>
                </div>
            </div>
            <div class="rt-slider" id="routeSlider"></div>
        </div>
        
        <div id="routeMapContainer"><?php

            include '../includes/routes/map.php'; ?>

        </div> <?php

            include '../includes/routes/profile.php';

            include '../includes/routes/itinerary.php';

        ?>
    </div>

<script src="/scripts/vendor.js"></script>
<script type="module" src="/scripts/routes/route.js"></script>
<script src="/scripts/routes/delete.js"></script>

</body>
</html>
