<?php

require '../actions/users/signup.php';
require '../actions/rides/official.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />
<link rel="stylesheet" href="/assets/css/ride.css" />

<body class="black-theme"> <?php

    include '../includes/navbar.php'; ?>

    <div class="container home-container end">

        <div class="company-quote">日帰りツアーカレンダー</div>

        <p>年間を通じて、地方の魅力を探索するサイクリングツアーを開催しております。</p>

        <p>CyclingFriendsのロゴマークの四色が意味している、「<strong>景色</strong>、<strong>食</strong>、<strong>分化</strong>、<strong>仲間</strong>」の４柱を主軸に、思い出に深く残るコンテンツづくりにこだわっています。</p>

        <p>お好きなライド名をクリックすると、詳細ページにアクセスできます。参加するには、アカウントを新規作成／ログインの上、詳細ページに表示されている「参加」ボタンをクリックし、必要情報を入力して頂くだけです。</p>
        
        <div class="rd-calendar">
            <div class="rd-cd-th"></div>
            <div class="rd-cd-th">年</div>
            <div class="rd-cd-th">月</div>
            <div class="rd-cd-th">日</div>
            <div class="rd-cd-th">タイトル</div>
            <div class="rd-cd-th">場所</div>
            <div class="rd-cd-th">距離</div>
            <div class="rd-cd-th">地形</div>
            <div class="rd-cd-th">ガイド</div>
            <div class="rd-cd-hr"></div><?php
            foreach ($rides as $ride) {
                $date = new DateTime($ride->date); ?>
                <div class="rd-cd-td cd-status <?= $ride->getStatusClass() ?>"><?= $ride->status ?></div>
                <div class="rd-cd-td cd-year"><?= $date->format('Y') ?></div>
                <div class="rd-cd-td cd-month"><?= $date->format('m') ?></div>
                <div class="rd-cd-td cd-day"><?= $date->format('d'). '（' .getWeekDay($date). '）' ?></div>
                <a class="rd-cd-td cd-title <?php
                    if ($ride->hasFeaturedImage()) echo 'with-img' ?>" <?php
                    if ($ride->hasFeaturedImage()) echo ' style="--bg-image: url(' .$ride->getFeaturedImage()->url. ');"' ?> 
                    href="<?= $router->generate('ride-single', ['ride_id' => $ride->id]) ?>">
                    <div class="cd-title-text"><?= $ride->name ?></div>
                </a>
                <div class="rd-cd-td cd-place"><?= $ride->meeting_place ?></div>
                <div class="rd-cd-td cd-distance"><?= $ride->distance. ' km' ?></div>
                <div class="rd-cd-td cd-terrain"><?= $ride->getTerrainIcon() ?></div>
                <div class="rd-cd-td cd-guides"><?php
                    if (count($ride->getGuides()) > 0) echo '<div class="cd-guides-label">ガイド：</div>';
                    foreach ($ride->getGuides() as $guide) $guide->getPropicElement(30, 30) ?>
                </div> <?php
            } ?>
        </div> <?php

        // Space for general error messages
        include '../includes/result-message.php'; ?>

    </div> <?php

include '../includes/foot.php'; ?>

</body>
</html>