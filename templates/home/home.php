<?php

require '../actions/users/signupAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />
<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
	#homeSceneryMap.click-map:hover {
		background-color: #f8b2c6;
	}
	#homeSegmentMap.click-map:hover {
		background-color: #edef9c;
	}
</style>

<body>
    <div class="black-theme"> <?php

    /* TEST SPACE */

    echo '<div style="color: white; background-color: black">';

    $garmin = new Garmin();

    echo '</div>';

    /* TEST SPACE */

    include '../includes/navbar.php';

    // Space for general error messages
    include '../includes/result-message.php'; ?>

    <!-- Animated background -->
    <div class="main js-fade-on-scroll js-overlay-top" data-overlay-color="#000000">
        <div class="home-video">
            <video autoplay muted loop>
                <source src="/media/overall.mp4" type="video/mp4">
            </video>
        </div>
    </div>

    <!-- Main container -->
    <div class="container smaller home-main-container js-fade-on-scroll">
        <h1 class="home-brand">cyclingfriends</h1>
        <div class="home-version">v0.3 (Public beta release)</div>
    </div>
            
    <div class="home-top-container home-slide px-0 pt-5">
        <h2 class="home-main-title text-center">
            サイクルツーリズムを支える新プラットフォーム。
        </h2> <?php
        include "../includes/home/carousel.php"; ?>
        <div class="home-schedule-container">
            <div class="home-schedule-block" style="border-color: #00e06e;">
                <div class="home-schedule-subtitle">Stage 1</div>
                <div class="home-schedule-title" style="color: #00e06e;">プライベートベータ公開</div>
                <p>終了</p>
            </div>
            <svg height="60" width="10">
                <polygon points="0,00 10,30 0,60" />
            </svg>
            <div class="home-schedule-block" style="border-color: #ff5555;">
                <div class="home-schedule-subtitle">Stage 2</div>
                <div class="home-schedule-title" style="color: #ff5555;">ベータ公開</div>
                <p>開始しました！</p>
            </div>
            <svg height="60" width="10">
                <polygon points="0,00 10,30 0,60" />
            </svg>
            <div class="home-schedule-block">
                <div class="home-schedule-subtitle">Stage 3</div>
                <div class="home-schedule-title">v.1.0 公開</div>
                <p>2024年以降</p>
            </div>
        </div>
    </div>

    <!-- Main container -->
    <div class="home-container home-slide p-0">
        <div class="home-slide container bg-transparent">
            <div class="js-fade-on-scroll" data-reverse="true">
                <h1 class="home-main-title">
                    物狂おしい社会からすり抜けて、<br>世界の美しさを追い求める。
                </h1>
                <p>CyclingFriendsとは、この道をともに選んだ仲間たち。</p>
                <p>君も、この旅を共にしませんか？</p>
            </div>
            <form class="container smaller connection-container" method="post" action="<?= $router->generate('user-signup') ?>">                
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
                    <label class="form-label" for="floatingInput">メールアドレス</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="login" class="form-control" id="floatingInput" placeholder="Login" name="login">
                    <label class="form-label" for="floatingInput">ユーザーネーム</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
                    <label class="form-label" for="floatingPassword">パスワード</label>
                </div>
                <button type="submit" class="btn button button-primary fullwidth" name="validate">アカウント作成</button>
                <div class="mt-4 sign-link">
                    <a href="<?= $router->generate('user-signin') ?>">既にアカウントをお持ちの方はこちら</a>
                </div>
            </form>
        </div>
    </div> <?php

    include '../includes/foot.php'; ?>

</div>

</body>
</html>

<script src="/assets/js/fade-on-scroll.js"></script>
<script src="/scripts/home/home.js"></script>