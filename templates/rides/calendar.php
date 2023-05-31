<?php

require '../actions/users/signupAction.php';
require '../actions/rides/officialAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />

<body class="black-theme"> <?php

    include '../includes/navbar.php'; ?>

    <div class="container home-container end">
        
        <div class="rd-calendar">
            <div class="rd-cd-th"></div>
            <div class="rd-cd-th">年</div>
            <div class="rd-cd-th">月</div>
            <div class="rd-cd-th">日</div>
            <div class="rd-cd-th">タイトル</div>
            <div class="rd-cd-th">場所</div>
            <div class="rd-cd-th">距離</div>
            <div class="rd-cd-th">地形</div>
            <div class="rd-cd-th">チーフ</div>
            <div class="rd-cd-th">アシスタント</div>
            <div class="rd-cd-hr"></div><?php
            foreach ($rides as $ride) {
                $date = new DateTime($ride->date) ?>
                <div class="rd-cd-td cd-status <?= $ride->getStatusClass() ?>"><?= $ride->status ?></div>
                <div class="rd-cd-td cd-year"><?= $date->format('Y') ?></div>
                <div class="rd-cd-td cd-month"><?= $date->format('m') ?></div>
                <div class="rd-cd-td cd-day"><?= $date->format('d') ?></div>
                <a class="rd-cd-td cd-title" href="<?= $router->generate('ride-single', ['ride_id' => $ride->id]) ?>"><?= $ride->name ?></a>
                <div class="rd-cd-td cd-place"><?= $ride->meeting_place ?></div>
                <div class="rd-cd-td cd-distance"><?= $ride->distance. ' km' ?></div>
                <div class="rd-cd-td cd-terrain"><?= $ride->getTerrainIcon() ?></div>
                <div class="rd-cd-td cd-chief">-</div>
                <div class="rd-cd-td cd-assistant">-</div> <?php
            } ?>
        </div> <?php

        // Space for general error messages
        include '../includes/result-message.php'; ?>

    </div> <?php

include '../includes/foot.php'; ?>

</body>
</html>