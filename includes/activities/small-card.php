<?php

$featured_image = $activity->getFeaturedImage() ?>
<a class="acsm-container interactive" href="/activity/<?= $activity->id ?>">
    <div class="acsm-photo"> <?php
        if ($featured_image) { ?>
            <img src="<?= $featured_image->url ?>" /> <?php
        } else { ?>
            <img src="/media/default-photo-<?= rand(0, 9) ?>.svg" /> <?php
        } ?>
    </div>
    <div class="acsm-infos">
        <div class="acsm-title"><?= $activity->title ?></div> - <div class="acsm-datetime"><?= $activity->datetime->format('Y/m/d') ?></div>
        <div class="acsm-line"><div class="acsm-distance">距離 : <?= $activity->route->distance ?>km</div><div class="acsm-duration">時間 : <?= $activity->duration->h. 'h' .$activity->duration->i ?></div></div>
        <div class="acsm-story"><?= $activity->getFirstStory() ?></div>
    </div>
</a>