<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />
<link rel="stylesheet" href="/assets/css/lightbox-style.css" />
<link rel="stylesheet" href="/assets/css/map-sidebar.css" />
<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body>

<?php include '../includes/navbar.php'; ?>

<!-- Animated background -->
<div class="main js-fade-on-scroll" data-overlay-color="#b2e0e5">
	<div class="with-background-img">
        <div class="home-video">
            <video autoplay muted loop>
                <source src="/media/overall.mp4" type="video/mp4">
            </video>
        </div>
		<!-- <div class="container-fluid end with-background-flash"></div> -->
	</div>
    <div class="js-scenery-info home-scenery-info" style="font-size: unset"></div>
</div>

<!-- Main container -->
<div class="container smaller home-main-container">
    <div class="home-site-logo js-fade-on-scroll">
        <img src="/media/cf.png" />
        <h1 class="home-brand">cyclingfriends</h1>
    </div>
        
    <div class="home-main-caption"><!--
        <p>新たな世界へようこそ！</p>
        <p>cyclingfriendsは、自転車を楽しむことで、日本の地方を元気にするコミュニティです。</p>
        <p>自然の大切さを思い出し、持続可能な社会づくりへの第一歩を踏み出しましょう。</p>--><!--
        <p>自然資源が消滅していく一方で、消費社会が桁違いの成長を見せていき、原点からドンドン離れていく。</p>
        <p>我々は一体、何を望んでいるのでしょうか？</p>
        <p>大きなパラダイムチェンジが求められているわけですが、表面的な施策だけではこのパラダイムチェンジは起きません。求められているのは、我々の価値観の改革です。</p>
        <p>cyclingfriendsは、自転車の活用を通じて、誰もが見失ってしまっていた日本の魅力を見つけだす旅で共にしてくれる仲間たちです。</p>
        <p>この旅に一緒に出掛けてみませんか？</p>-->
        <div class="classy-title" style="text-shadow: 0 0 10px black;">2023年始動。</div>
    </div>
</div>

<!-- Bottom container -->
<div class="bg-lightgrey">
    <div class="container home-slide text-center js-fade-on-scroll" data-overlay-color="#f8b2c6" data-overlay-color2="#b2e0e5">
        <h2>cyclingfriendsとは？</h2>
        <div class="schema">
            <div class="shema-part">
                <div class="shema-title">Remote</div>
                <div class="shema-table">
                    <div class="shema-row">全国のサイクリングガイドマップ</div>
                    <div class="shema-row">日本の絶景スポット＆美しい道事典</div>
                    <div class="shema-row">走行日記の管理＆共有</div>
                    <div class="shema-row">ルート開拓ツール</div>
                    <div class="shema-row">グループライド開催ツール</div>
                </div>
            </div>
            <div class="shema-cross">x</div>
            <div class="shema-part">
                <div class="shema-title">Real</div>
                    <div class="shema-row">全国の魅力を探究するサイクリングツアーの開催</div>
                    <div class="shema-row">サイクリングガイドの養成</div>
                    <div class="shema-row">サイクリングライフの普及活動</div>
                <div class="shema-table"></div>
            </div>
        </div>
        <p>森は数百年単位、哺乳動物は数十年単位、そして昆虫は数年単位で、半分以上地球から絶滅したと言われている一方で、消費の拡大が止まらない我々人間の社会。<br>
        我々人間は一体、何を望んでいるのだろうか？</p>
        <p>次世代に継ぐ地球を守るために、求められるのは我々の価値観そのものの変革であり、もはや表面的な施策ではない。</p>
        <p>cyclingfriendsとは、失われつつある日本、世界の大切な「なにか」を見つけ出すために、旅を共にしてくれる仲間たち。</p>
        <p>この旅を共にしませんか？</p>
    </div>
    <div class="js-fade-on-scroll" data-overlay-color="#edef9c" data-overlay-color2="#f8b2c6">
    <div class="cf-map" id="homeMap"></div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>
<script src="/assets/js/fade-on-scroll.js"></script>
<script src="/scripts/home/home.js"></script>
<script type="module" src="/scripts/home/map.js"></script>