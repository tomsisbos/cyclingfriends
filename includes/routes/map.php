<div class="cf-map" id="routeMap" loading="lazy" data-id="<?= $route->id ?>" <?php
    if (isset($_SESSION['auth']) && $connected_user->isPremium()) echo 'interactive="true"' ?>> <?php
    if (!isset($_SESSION['auth']) || !$connected_user->isPremium()) { ?>
        <a class="staticmap" href="<?= $_SERVER['REQUEST_URI']. '/signin'?>"><img /></a> <?php
    } ?>
</div>
<div class="grabber"></div>