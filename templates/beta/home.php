<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />
<style>
	.with-background-img::before {
		background: var(--bgImage);
	}
</style>

<body>

<?php include '../includes/navbar.php'; ?>

<!-- Animated background -->
<div class="main js-fade-on-scroll" data-overlay-color="#70c6ab">
	<div class="with-background-img">
		<div class="container-fluid end with-background-flash"></div>
	</div>
    <div class="js-scenery-info home-scenery-info" style="font-size: unset"></div>
</div>

<!-- Main container -->
<div class="container smaller home-main-container">
    <div class="home-site-logo js-fade-on-scroll">
        <img src="/media/cf.png" />
        <h1 class="home-brand">cyclingfriendsとは？</h1>
    </div>
        
    <div class="home-main-caption"><!--
        <p>新たな世界へようこそ！</p>
        <p>cyclingfriendsは、自転車を楽しむことで、日本の地方を元気にするコミュニティです。</p>
        <p>自然の大切さを思い出し、持続可能な社会づくりへの第一歩を踏み出しましょう。</p>-->
        <p>自然資源が消滅していく一方で、消費社会が桁違いの成長を見せていき、原点からドンドン離れていく。</p>
        <p>我々は一体、何を望んでいるのでしょうか？</p>
        <p>大きなパラダイムチェンジが求められているわけですが、表面的な施策だけではこのパラダイムチェンジは起きません。求められているのは、我々の価値観の改革です。</p>
        <p>cyclingfriendsは、自転車の活用を通じて、誰もが見失ってしまっていた日本の魅力を見つけだす旅で共にしてくれる仲間たちです。</p>
        <p>この旅に一緒に出掛けてみませんか？</p>
        <div class="home-release-date">2023年4月にベータバージョンを公開予定</div>
    </div>
</div>

<!-- Bottom container -->
<div class="bg-lightgrey">
    <div class="container home-slide js-fade-on-scroll" data-overlay-color="#b2e0e5">
        <h2>2023年4月にベータバージョン公開！</h2>
        <p>約1000年で哺乳動物の8割が消滅しました。</p>
        <p>約1000年で植物の8割が消滅しました。</p>
        <p>約100年で虫の8割が消滅しました。</p>
        <p>自然資源が消滅していく一方で、消費社会が桁違いの成長を見せていき、原点からドンドン離れていく。</p>
        <p>我々は一体、何を望んでいるのでしょうか？</p>
        <p>大きなパラダイムチェンジが求められているわけですが、表面的な施策だけではこのパラダイムチェンジは起きません。求められているのは、我々の価値観の改革です。</p>
        <p>cyclingfriendsは、自転車の活用を通じて、誰もが見失ってしまっていた日本の魅力を見つけだす旅で共にしてくれる仲間たちです。</p>
        <p>この旅に一緒に出掛けてみませんか？</p>
    </div>
    <div class="cf-map">map</div>
</div>

</body>
</html>

<script src="/assets/js/animated-img-background.js"></script>
<script src="/assets/js/fade-on-scroll.js"></script>