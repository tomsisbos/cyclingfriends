<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php';
include '../actions/routes/routeAction.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />

<body class="relative-navbar">

	<?php include '../includes/navbar.php'; ?>

    <div class="main container-shrink"> <?php
            
        if ($route->getFeaturedImage() !== null) { ?>
            <div class="container header" style="background-image: <?= 'url(' .$route->getFeaturedImage()->url. '); background-size: cover;' ?>"> <?php
        } else { ?>
            <div class="container" style="background-color: #bbb;"> <?php
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
                    } ?>
                    <a id="export" download>
                        <button class="mp-button success" type="button">エクスポート</button>
                    </a> <?php
                    if ($route->author->id == $connected_user->id) { ?>
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
                    <div><strong>集合場所 : </strong><?= $route->startplace ?></div>
                    <div><strong>解散場所 : </strong><?= $route->goalplace ?></div>
                </div>
                <div class="spec-column">
                <div><strong>距離 : </strong><?= round($route->distance, 1) ?>km</div>
                    <div><strong>獲得標高 : </strong><?= $route->elevation ?>m</div>
                </div>
                <div class="spec-column">
                    <div><strong>予測時間 : </strong><?= $route->calculateEstimatedTime($connected_user->level)->format('H:i') ?></div>
                    <div><strong>難易度 : </strong><?= $route->getStars($route->calculateDifficulty()) ?></div>
                </div>
            </div>
            <div class="rt-slider"></div>
        </div>
        <div id="routePageMapContainer">
            <div class="cf-map" id="routePageMap" <?php if ($connected_user->isPremium()) echo 'interactive="true"' ?>> <?php
                if (!$connected_user->isPremium()) { ?>
                    <img class="staticmap"></img> <?php
                } ?>
            </div>
            <div class="grabber"></div>
        </div>
        <div id="profileBox" class="container p-0" style="height: 20vh; background-color: white;">
            <canvas id="elevationProfile"></canvas>
        </div>
        <div class="container p-0 spec-table-container">
            <div class="spec-table">
                <table id="routeTable">
                    <tbody>
                        <tr class="spec-table-th">
                            <th class="table-element e10 text-left">距離</th>
                            <th class="table-element e40 text-left">名称</th>
                            <th class="table-element e20 text-center">場所</th>
                            <th class="table-element e15 text-center">標高</th>
                            <th class="table-element e25 text-center">コースまで</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<script src="/scripts/map/vendor.js"></script>
<script type="module" src="/map/class/CFUtils.js"></script>
<script type="module" src="/scripts/routes/route.js"></script>
<script src="/scripts/routes/delete.js"></script>

</body>
</html>
