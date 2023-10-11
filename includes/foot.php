<div class="footer">

    <div class="container bg-transparent cf-footer">
        <div class="cf-footer-block">
            <a class="f-head" href="/company"><div>会社について</div></a>
            <a href="/company/business"><div>事業構想</div></a>
            <a href="/news"><div class="maintem">ニュース</div></a>
            <a href="/company/contact"><div>お問い合わせ</div></a>
            <a href="/company/commerce-disclosure"><div>特定商取引法に基づく表記</div></a>
        </div>
        <div class="cf-footer-block">
            <a class="f-head" href="/manual"><div>マニュアル</div></a> <?php
            foreach (Manual::$chapters as $slug => $chapter) { ?>
                <a href="/manual/<?= $slug ?>"><div><?= $chapter['title'] ?></div></a> <?php
            } ?>
        </div>
        <div class="cf-footer-block">
            <a class="f-head" href="<?= $router->generate('rides-calendar') ?>"><div>ツアー</div></a>
            <a href="<?= $router->generate('rides-calendar') ?>"><div>スケジュール</div></a>
            <a href="<?= $router->generate('ride-contract') ?>"><div>ツアー規約</div></a>
        </div>
    </div>

    <div class="container bg-transparent">
        <div class="footer-text">© 2023 Terra Incognita</div>
        <div class="footer-logo">
            <img src="/media/cf.png" />
            <div class="footer-brand">cyclingfriends</div>
        </div>
    </div>
    
</div>