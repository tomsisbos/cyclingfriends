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

    include '../includes/navbar.php'; ?>

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
        <div class="home-version">v0.0</div>
        <div class="classy-title">
            2023年始動
        </div>
    </div>

    <!-- Main container -->
    <div class="home-top-container">
        <div class="container">
            <div class="home-slide">
                <div class="js-fade-on-scroll" data-reverse="true">
                    <h1 class="home-main-title">
                        物狂おしい社会からすり抜けて、<br>世界の美しさを追い求める。
                    </h1>
                    <p>CyclingFriendsとは、この道をともに選んだ仲間たち。</p>
                    <p>君も、この旅を共にしませんか？</p>
                </div>
                <form class="container smaller connection-container" method="post">                
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
                    <div class="mt-4 text-center">
                        <a href="<?= $router->generate('user-signin') ?>">既にアカウントをお持ちの方はこちら</a>
                    </div>
				</form>
            </div>
        </div>
    </div>
            
    <div class="home-container home-slide p-0">
        <h2 class="home-main-title text-center">
            サイクルツーリズムを支える新プラットフォーム。
        </h2> <?php
        include "../includes/home/carousel.php"; ?>
    </div> <?php

    include '../includes/foot.php'; ?>

</div>

</body>
</html>

<script src="/assets/js/fade-on-scroll.js"></script>
<script src="/scripts/home/home.js"></script>