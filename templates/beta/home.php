<?php

require '../actions/users/registerMailAction.php';
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

<body> <?php

include '../includes/navbar.php'; ?>

<!-- Animated background -->
<div class="main js-fade-on-scroll js-overlay-top" data-overlay-color="#b2e0e5">
    <div class="home-video">
        <video autoplay muted loop>
            <source src="/media/overall.mp4" type="video/mp4">
        </video>
    </div>
    <div class="js-scenery-info home-scenery-info" style="font-size: unset"></div>
</div>

<!-- Main container -->
<div class="container smaller home-main-container">
    <div class="home-main-text">
        <p>森は数百年単位、哺乳動物は数十年単位、そして昆虫は数年単位で、半分以上地球から絶滅したと言われている一方で、消費の拡大が止まらない我々人間の社会。</p>
        <p>この物狂おしい社会からすり抜けて、世界の美しさを追い求めることに全てを注ぐ。それは、サイクリングが実現する世界。</p>
        <p>CyclingFriendsとは、この世界をともに選んだ仲間たち。</p>
        <p>君も、この旅を共にしませんか？</p>
        <a class="tag" href="https://www.facebook.com/profile.php?id=100089569884711" target="_blank">Facebook</a>・<a class="tag" href="https://twitter.com/cyclingfds" target="_blank">Twitter</a>
    </div>
    <div class="home-site-logo js-fade-on-scroll">
        <img src="/media/cf.png" />
        <h1 class="home-brand">cyclingfriends</h1>
    </div>
    <div class="home-main-caption">
        <div class="classy-title">2023年始動。</div>
    </div>
</div>

<!-- Bottom container -->
<div class="bg-lightgrey">

    <div class="container home-slide text-center js-fade-on-scroll" data-overlay-color="#f8b2c6" data-overlay-color2="#b2e0e5">
        <h2>事業構想</h2>
        <div class="schema">
            <div class="schema-part">
                <div class="schema-title">Real</div>
                <div class="schema-table">
                    <div class="schema-row">サイクリングガイドの養成</div>
                    <div class="schema-row">全国の魅力を探究するサイクリングツアー</div>
                    <div class="schema-row">レンタルサイクルや施設運営...等</div>
                </div>
            </div>
            <div class="schema-cross">x</div>
            <div class="schema-part">
                <div class="schema-title">Remote</div>
                <div class="schema-table">
                    <div class="schema-row">オンラインサイクリングガイドマップ</div>
                    <div class="schema-row">日本の絶景スポット＆美しい道事典</div>
                    <div class="schema-row">走行日記の管理＆共有機能</div>
                    <div class="schema-row">ルート作成ツール</div>
                    <div class="schema-row">グループライド開催ツール...等</div>
                </div>
            </div>
        </div>
        <div class="home-text">
            <p>日本の観光産業が直面している大きな課題のひとつは「生産性向上」。<br>
            ここ数年で観光に対する考え方が著しく変化しているため、従来の経営ノウハウから脱却し、変革する必要があります。</p>
            <!--<p>2019年から「<a href="https://www.mlit.go.jp/common/001284799.pdf" target="_blank">観光産業の生産性向上推進事業</a>」を進めている観光庁は、旅行サービスの高度化を目指す施策の主軸を、（１）国内の隠れた観光資源の発掘と、（２）個人の好みを踏まえたより高品質な旅行・宿泊サービスの開発や適正価格での提供と定義しています。</p>-->
            <p>時代の変化に合った「リアル」な観光体験にこだわりを持ちながらも、CyclingFriendsは「リモート」でサイクルツーリズムを支える様々なサービスを提供していきます。</p>
            <p>サイクルツーリズムが持ち合わせる可能性を最大限に活かすことで、新たな価値提供を創出し、「旅行会社」の概念を一新させます。</p>
        </div>
    </div>

    <div class="container home-slide text-center js-fade-on-scroll" data-overlay-color="#edef9c" data-overlay-color2="#f8b2c6">
        <h2>全国の絶景スポット</h2>
        <div class="home-columns">
            <div class="home-column-2">
                <div class="home-text">
                    <p>サイクリングとは、消費社会で言われている「不要不急」をわざわざ追い求めるライフスタイルのこと。用のない地域を訪れ、値段のない自然を楽しむ：これは消費社会とは真逆の、サイクリストの不思議な志。</p>
                    <p>だからこそ、徒歩ではいけない、車では感じられない地域を、サイクリストは誰より知っているのです。</p>
                    <p>コミュニティの皆さんが見つけ出した全国の絶景スポットを事典化し、データの有効的な発信や活用を通じて、地方活性化に寄与していきます。</p>
                    <p>サイクリングを楽しむだけで価値を生み出せるようになると、サイクリストの社会的地位が確立され、「自転車社会」に近づいていきます。脱炭素の実現はもちろんのこと、自然を大切にする価値観の醸成にも繋がると考えています。</p>
                </div>
            </div>
            <div class="home-column-2 home-column-map">
                <div class="cf-map click-map" id="homeSceneryMap">
                    <div class="click-map-text">Click to preview...</div>
                </div>
            </div>
        </div>
    </div>

    <div class="container home-slide text-center js-fade-on-scroll" data-overlay-color="#6bc6ab" data-overlay-color2="#edef9c">
        <h2>日本の最も美しい道</h2>
        <div class="home-columns">
            <div class="home-column-2 home-column-map">
                <div class="cf-map click-map" id="homeSegmentMap">
                    <div class="click-map-text">Click to preview...</div>
                </div>
            </div>
            <div class="home-column-2">
                <div class="home-text">
                    <p>2019年から「<a href="https://www.mlit.go.jp/common/001284799.pdf" target="_blank">観光産業の生産性向上推進事業</a>」を進めている観光庁は、旅行サービスの高度化を目指す施策の主軸を、（１）国内の隠れた観光資源の発掘と、（２）個人の好みを踏まえたより高品質な旅行「...」の開発や適正価格での提供と定義しています。</p>
                    <p>「日本で最も美しい道」は、景観や文化等の観点から優れた走行体験を提供してくれる道のデータを集め、様々な形で提供するサービスです。</p>
                    <p>そのデータをセグメントの形で地図上に落とし込んだり、紹介記事を自動作成したり、自分が走行したセグメントを記録したりできるようになります。
                    <p>キャンペーン開催を通じて、地域の活性化に貢献する取り組みも行っていきます。</p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container home-slide text-center js-fade-on-scroll" data-overlay-color2="#6bc6ab">
        <h2>本格スタートに向けて</h2>
        
        <div class="home-schedule-container">
            <div class="home-schedule-block">
                <div class="home-schedule-subtitle">Stage 1</div>
                <div class="home-schedule-title">プライベートベータ公開</div>
                <p>2023年2月予定</p>
            </div>
            <svg height="60" width="10">
                <polygon points="0,00 10,30 0,60" />
            </svg>
            <div class="home-schedule-block">
                <div class="home-schedule-subtitle">Stage 2</div>
                <div class="home-schedule-title">ベータ公開</div>
                <p>2023年春以降</p>
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

        <p>これから本格スタートに向けて、メールを通じてご案内致しますので、気になる方は是非登録してみましょう！</p>
    
        <form class="container smaller connection-container" method="post" id="registerMail" action="/#registerMail"> <?php
            
            if (isset($errormessage)) echo '<div class="error-block"><p class="error-message">' .$errormessage. '</p></div>';
            if (isset($successmessage)) echo '<div class="success-block"><p class="success-message">' .$successmessage. '</p></div>'; ?>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
                <label class="form-label" for="floatingInput">Email address</label>
            </div>

            <button type="submit" class="btn button fullwidth button-primary" name="validate">Register</button>

        </form>
    </div>
</div> <?php

include '../includes/foot.php'; ?>

</body>
</html>

<script src="/assets/js/fade-on-scroll.js"></script>
<script src="/scripts/home/home.js"></script>
<script type="module" src="/scripts/home/scenery-map.js"></script>
<script type="module" src="/scripts/home/segment-map.js"></script>

<?php

    require_once "../actions/blobStorageAction.php";
    include "../actions/databaseAction.php";
    
    ini_set('memory_limit', '1024M');
    ini_set('max_execution_time', '700');

    die();

    // Upload file
    $blobClient->createBlockBlob($container_name, $blob_name, $stream);

    // Set metadata
    $blobClient->setBlobMetadata($container_name, $blob_name, $metadata);

    // Get blob
    $img_src = $blobClient->getBlobUrl($container_name, '20230108_095455.jpg');











    die();

    // Prepare variables
    $container_name = 'cyclingfriends-data';
    $blob_name = 'HelloWorldTxt';
    $stream = fopen("HelloWorld.txt", "r");
    $metadata = [
        'folder' => 'test'
    ];

    // Upload file
    $blobClient->createBlockBlob($container_name, $blob_name, $stream);

    // Set metadata
    $blobClient->setBlobMetadata($container_name, $blob_name, $metadata);    

    // Get blob
    $img_src = $blobClient->getBlobUrl($container_name, '20230108_095455.jpg');

?>