<div class="cf-map" id="routeMap" loading="lazy" data-id="<?= $route->id ?>" <?php
    /*if (isSessionActive() && getConnectedUser()->isPremium())*/ echo 'interactive="true"' ?>> <?php
    /*if (!isSessionActive() || !getConnectedUser()->isPremium()) { ?>
        <a class="staticmap" href="<?= $_SERVER['REQUEST_URI']. '/signin'?>"><img /></a> <?php
    } */?>
</div>
<div class="grabber"></div>