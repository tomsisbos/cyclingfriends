<div class="sg-card" data-id="<?= $segment->id ?>">
    <a href="/segment/<?= $segment->id ?>">
        <div class="sg-imgs-container">
            <div class="sg-map-thumbnail"><img src="<?= $segment->route->getThumbnail() ?>"></div>
            <div class="sg-photo"><img src="<?= $segment->getFeaturedImage() ?>"></div>
        </div>
    </a>
    <div class="sg-data">
        <div class="sg-top"> 
            <div class="sg-top-text">
                <a href="/segment/<?= $segment->id ?>"><div class="sg-title"><?= $segment->name ?></div></a> <?php
                $cleared_activity_id = $segment->isCleared();
                if ($cleared_activity_id) { ?>
                    <div id="visited-icon" style="display: inline;" title="このセグメントを訪れました。">
                        <a href="/activity/<?= $cleared_activity_id ?>" target="_blank">
                            <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                        </a>
                    </div> <?php
                } ?>
                <div class="sg-place-elevation"><?= $segment->route->startplace->toString() . ' - ' . round($segment->route->distance, 1) . 'km' ?></div>
            </div>
        </div>
        <div class="sg-description"><?= $segment->description ?></div>
    </div>
</div>