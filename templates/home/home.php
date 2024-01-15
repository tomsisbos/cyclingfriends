<?php

require '../actions/users/signup.php';
include '../actions/users/initPublicSession.php';
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

    include '../includes/navbar.php';

    // Space for general error messages
    include '../includes/result-message.php'; ?>

    <!-- Animated background -->
    <div class="main js-fade-on-scroll js-overlay-top" data-overlay-color="#000000">
        <div class="home-video">
            <video autoplay muted loop>
                <source src="/media/videos/overall.mp4" type="video/mp4">
            </video>
        </div>
    </div>

    <!-- Main container -->
    <div class="container smaller home-main-container js-fade-on-scroll">
        
        <div class="home-overlay" style="z-index: 2">
            <h1 class="home-brand">cyclingfriends</h1>
            <div class="home-version">v0.8 (Public beta release)</div>
            <div class="home-appeal home-version text-center">
                <p class="home-appeal" style="padding: 5px">モバイルアプリが公開されました！</p>
                <div class="mobile-download-buttons">
                    <a href="https://play.google.com/store/apps/details?id=com.cyclingfriends.cyclingfriendsmobile&fbclid=IwAR2FHHsOj2SyRgNR0mnPOoiiZ-MZT_r8cpNAxbuxfUCoFP-S7hQhUZ1N0gE&pli=1"><img class="mobile-download-app-image" src="/media/google_play.png" /></a>
                    <a href="https://apps.apple.com/us/app/cyclingfriends/id6469093820"><img class="mobile-download-app-image" src="/media/app_store.png" /></a>
                </div>
            </div>
        </div>

        <div class="home-overlay" style="z-index: 0">
            <div class="mobile-demo">
                <img class="mobile-demo-image" src="/media/mobile_frame.png" />
                <video class="mobile-demo-video" autoplay muted loop>
                    <source src="/media/videos/mobile_demo.mp4" type="video/mp4">
                </video>
            </div>
        </div>

    </div>

            
    <div class="home-top-container home-slide px-0 pt-5">
        <h2 class="home-main-title text-center">
            <div class="home-catchcopy" style="z-index: 1">
                <p class="first">みんなで作る</p><p class="second">サイクリングマップ！</p>
            </div>
        </h2>
        <div class="home-video-container">
            <iframe width="560" height="400" src="https://www.youtube-nocookie.com/embed/qydGgXVx4Cw?si=asonk6u_4TGJhBwT" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
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