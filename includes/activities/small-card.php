<?php

$featured_image = $activity->getFeaturedImage() ?>
<a class="acsm-container" href="/activity/<?= $activity->id ?>">
    <div class="acsm-photo"> <?php
        if ($featured_image) { ?>
            <img src="data:<?= $featured_image->type ?>;base64,<?= $featured_image->blob ?>" /> <?php
        } else { ?>
            <img src="/media/default-photo-<?= rand(0, 9) ?>.svg" /> <?php
        } ?>
    </div>
    <div class="acsm-infos">
        <div class="acsm-title"><?= $activity->title ?></div> - <div class="acsm-datetime"><?= $activity->datetime->format('Y/m/d') ?></div>
        <div class="acsm-line"><div class="acsm-distance">Distance : <?= $activity->route->distance ?>km</div><div class="acsm-duration">Duration : <?= $activity->duration->format('H\hi') ?></div></div>
        <div class="acsm-story"><?= $activity->getFirstStory() ?></div>
    </div>
</a>