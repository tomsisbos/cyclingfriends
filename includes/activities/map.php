
<div id="activityMapContainer">
    <div class="cf-map" id="activityMap" loading="lazy"<?php if (isset($activity)) echo 'data-id="' .$activity->id. '"'; /*if (isSessionActive() && getConnectedUser()->isPremium())*/ echo 'interactive="true"' ?>> <?php 
        /*if (!isSessionActive() || !getConnectedUser()->isPremium()) { ?>
            <a class="staticmap" href="<?= $_SERVER['REQUEST_URI']. '/signin'?>"><img /></a> <?php
        } */?>
    </div>
    <div class="grabber"></div>
</div>
<div id="profileBox" class="container p-0" style="height: 22vh; background-color: white;">
    <canvas id="elevationProfile"></canvas>
</div>