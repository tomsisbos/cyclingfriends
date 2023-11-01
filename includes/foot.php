<div class="footer menu-link">

    <div class="container bg-transparent cf-footer">
        <div class="cf-footer-block">
            <a class="f-head interactive" href="/company"><div>会社について</div></a>
            <a class="interactive" href="/company/business"><div>事業構想</div></a>
            <a class="interactive" href="/news"><div class="maintem">ニュース</div></a>
            <a class="interactive" href="/company/contact"><div>お問い合わせ</div></a>
            <a class="interactive" href="/company/commerce-disclosure"><div>特定商取引法に基づく表記</div></a>
        </div>
        <div class="cf-footer-block">
            <a class="f-head interactive" href="/manual"><div>マニュアル</div></a> <?php
            foreach (Manual::$chapters as $slug => $chapter) { ?>
                <a class="interactive" href="/manual/<?= $slug ?>"><div><?= $chapter['title'] ?></div></a> <?php
            } ?>
        </div>
        <div class="cf-footer-block">
            <a class="f-head interactive" href="<?= $router->generate('rides-calendar') ?>"><div>ツアー</div></a>
            <a class="interactive" href="<?= $router->generate('rides-calendar') ?>"><div>スケジュール</div></a>
            <a class="interactive" href="<?= $router->generate('ride-contract') ?>"><div>ツアー規約</div></a>
            <a class="interactive" href="<?= $router->generate('bike-rental-contract') ?>"><div>バイクレンタル規約</div></a>
        </div>
        <div class="cf-footer-block">
            <a class="f-head interactive" href="/brands/poli"><div>取り扱いブランド</div></a>
            <a class="interactive" href="/brands/poli"><div>Poli</div></a>
        </div>
    </div>

    <div class="container bg-transparent">
        <div class="footer-brands">
            <a href="/brands/poli"><img src="/media/brands/poli/logo.jpg" /></a>
        </div>
        <div class="footer-text">© 2023 Terra Incognita</div>
        <div class="footer-logo">
            <img src="/media/cf.png" />
            <div class="footer-brand">cyclingfriends</div>
        </div>
    </div>
    
</div>