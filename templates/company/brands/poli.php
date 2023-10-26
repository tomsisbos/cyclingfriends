<?php

include '../actions/users/initPublicSession.php';
include '../includes/head.php';
include '../actions/company/brands/poli/contact.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />
<link rel="stylesheet" href="/assets/css/brand.css" />

<body> <?php

    include '../includes/navbar.php';
    
    // Space for general error messages
    include '../includes/result-message.php'; ?>

    <!-- Main container -->

    <img class="brand-logo" src="/media/brands/poli/logo.jpg" />

    <h1 class="text-center">カスタムウェアメーカー</h1>
    
    <div class="poli-slider" data-simple-slider>
        <img src="https://www.poli.fr/img/cms/HOME%20EN/Cycling-1.jpg"/>
        <img src="https://www.poli.fr/img/cms/HOME%20EN/Cycling-2.jpg"/>
        <img src="https://www.poli.fr/img/cms/HOME%20EN/Cycling-3.jpg"/>
        <img src="https://www.poli-teamwear.com/img/cms/HOME%20EN/Triathlon-1.jpg"/>
        <img src="https://www.poli-teamwear.com/img/cms/HOME EN/Trail-1.jpg"/>
    </div>
    
    <div class="brand container">

        <div class="container smaller">
            
            <p>1979年にフランスのマルセイユでファウンダーの「Michel POLI」氏から生まれたアパレルブランド。</p>
            <p>今では、スポーツ向けカスタムウェア市場において、<strong>本国フランスでトップシェアを誇っています</strong>。</p>
            <p>POLIの特徴は、企画やデザインから生産やカスタマーサポートまで、<strong>一切外注することなく、全て社内で完結させているところ</strong>です。そのおかげもあり、コロナ禍の中でも大きな成長を遂げ、世界各国でも事業を展開するようになりました。</p>
            <p>マルセイユといえば、日本人として初めてツール・ド・フランスを完走した別府史之氏をはじめ、多くの日本人を育ててきた「Vélo Club la Pomme Marseille」の伝統的なクラブチームを思い浮かぶ日本人は少なくないと思います。POLIも地元企業として、初期から共に成長してきたブランドであり、日本に対する愛着も大きく経験も豊富なので、日本でも事業を展開しないはずはありませんでした。</p>

        </div>

        <div class="container">

            <h2>公式ホームページ</h2>
            <a target="_blank" href="https://www.poli-teamwear.com/gb/">https://www.poli-teamwear.com/gb/</a><br>
            <p>商品ごとのサイズチャートやその他の細かい情報をご確認頂けます。</p>
            <p>※現在は英語になっております。日本語版を準備中です。</p>

            <h2>プロダクトカタログ</h2>
            <div class="poli-catalog">
                <div class="poli-catalog-block">
                    <img src="https://www.poli-teamwear.com/56842-large_default/jersey-cycling-cesar.jpg">
                    <div class="catalog-text">
                        <h3>自転車</h3>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/cycling/race.pdf">レース</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/cycling/premium.pdf">プレミアム</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/cycling/cyclo.pdf">一般</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/cycling/gravel.pdf">グラベル</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/cycling/bmx_mtb_cyclocross.pdf">BMX・MTB・シクロクロス</a><br>
                    </div>
                </div>
                <div class="poli-catalog-block">
                    <img src="https://www.poli-teamwear.com/57605-large_default/womens-crop-top-sports-stacy.jpg">
                    <div class="catalog-text">
                        <h3>陸上競技・ランニング・トレイル</h3>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/athle_running_trail/athle.pdf">陸上競技</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/athle_running_trail/running_trail.pdf">ランニング・トレイル</a><br>
                    </div>
                </div>
                <div class="poli-catalog-block">
                    <img src="https://www.poli-teamwear.com/59758-large_default/trifonction-femme-sans-manches-elite-sireen.jpg">
                    <div class="catalog-text">
                        <h3>トライアスロン</h3>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/triathlon/triathlon.pdf">トライアスロン</a><br>
                    </div>
                </div>
                <div class="poli-catalog-block">
                    <img src="https://www.poli-teamwear.com/40527-large_default/idol-unisex-rowing-skinsuit.jpg">
                    <div class="catalog-text">
                        <h3>ボート</h3>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/boat/boat.pdf">ボート</a><br>
                    </div>
                </div>
                <div class="poli-catalog-block">
                    <img src="https://www.poli-teamwear.com/20174-large_default/next-head-thingy.jpg">
                    <div class="catalog-text">
                        <h3>アクセサリー</h3>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/accessories/accessories.pdf">アクセサリー</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/accessories/accessories_2.pdf">アクセサリー（その他）</a><br>
                        ・<a target="_blank" href="/media/brands/poli/catalogue/accessories/sportswear.pdf">スポーツウェア</a><br>
                    </div>
                </div>
            </div>

            <h2>価格の計算について</h2>
            <p>注文内容（商品の枚数、商品グルーブの合計枚数、オプションなど）によって、価格が変動する仕組みとなっております。</p>
            <p>注文をする前に、注文書をご確認頂きます。最終的な価格はあくまでも注文書に記載されている内容となりますので、参考程度に思って頂ければ幸いです。</p>
            <h3>最低注文枚数</h3>
            <p><strong>商品グループ別：10枚以上</strong>（例：半袖ジャージ6枚と長袖ジャージ4枚はグループ1の合計枚数が10枚になるのでＯＫです。仮に半袖ジャージが6枚でビブショーツが4枚の場合、商品グループが違うので枚数を増やす必要があります）<br/>
            一番お得になってくるのは20枚以上です。</p>
            <p><strong>商品別：ひとつの商品の合計枚数が5枚以下となる場合、単価に15%を追加</strong>させて頂きます。</p>
            <h3>追加注文制度</h3>
            <p>ご注文頂いてから1年以内に同じデザインでの追加注文は1枚から可能で、15%上乗せされるのは3枚以下の場合だけです。※但し、最初の注文で取引のあった商品（オプションも同じ）に限ります。また、第1回注文と同じ価格帯で計算されます。</p>
            <iframe src="https://docs.google.com/spreadsheets/d/e/2PACX-1vTEsVSMVQruYzV1wbfyMwLtGiCcu6cFZhYgo008wbmrMa1yyUGXD9QDgqOqjlJgBqxKUwfX9e68mvI7/pubhtml?widget=true&amp;headers=false"></iframe>
            <p>※その他の競技の価格表は、順次更新していきます。</p>

            <h2>納期</h2>
            <p>ご注文から商品着手まで2ヶ月が目安です。</p>
            
            <form method="POST" class="container smaller">

                <h1 class="text-center mb-3">お問い合わせ</h1>

                <p>お見積りの依頼や、その他のご相談はこちらよりご連絡頂けますと幸いです。細かくサポートをさせて頂きます。</p> <?php

                include '../includes/contact-form.php'; ?>

            </form>

        </div>
    </div> <?php

    include '../includes/foot.php'; ?>

</body>
</html>

<script src="/assets/js/fade-on-scroll.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/simple-slider/1.0.0/simpleslider.min.js"></script>
<script>
    simpleslider.getSlider()
</script>