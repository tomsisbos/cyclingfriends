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
        <div class="acsm-title">
            <div class="acsm-propic"><?= $activity->getAuthor()->getPropicElement(30, 30, 0) ?></div>
            <?= $activity->title ?></div> - <div class="acsm-datetime"><?= $activity->datetime->format('Y/m/d') ?></div>
        <div class="acsm-line">
            <div class="acsm-distance">距離 : <?= round($activity->route->distance, 1) ?>km</div>
            <div class="acsm-duration">時間 : <?php
            if ($activity->duration->h > 0) echo $activity->duration->h. 'h' .$activity->duration->i;
            else echo $activity->duration->i. 'min' ?></div>
        </div>
        <div class="acsm-story"><?= $activity->getFirstStory() ?></div>
    </div>
</a>