<div class="ac-card">

    <div class="ac-main-container">

        <div class="ac-infos-container">
            <div class="ac-user-details">
                <div class="ac-user-propic">
                <a href="/rider/<?= $activity->user->id ?>"><?php $activity->user->displayPropic() ?></a>
                </div>
                <div class="ac-details">
                    <div class="ac-user-name">
                        <a href="/rider/<?= $activity->user->id ?>"><?= $activity->user->login ?></a>
                    </div>
                    <div class="ac-name">
                        <a href="/activity/<?= $activity->id ?>">
                            <?= $activity->title ?>
                        </a>
                    </div>
                    <div class="ac-posting-date">
                        <?= $activity->datetime->format('Y/m/d') . ' - from ' . $activity->getPlace()['start']->toString() . ' to ' . $activity->getPlace()['goal']->toString(); ?>
                    </div>
                </div>
            </div>
            <div class="ac-specs">
                <div class="ac-spec <?= $activity->setBackgroundColor('distance')?> ">
                    <div class="ac-spec-label"><strong>Distance : </strong></div>
                    <div class="ac-spec-value"><?= round($activity->route->distance, 1) ?><span class="ac-spec-unit"> km</span></div>
                </div>
                <div class="ac-spec <?= $activity->setBackgroundColor('duration')?> ">
                    <div class="ac-spec-label"><strong>Duration : </strong></div>
                    <div class="ac-spec-value"> <?php
                        if (substr($activity->duration->format('H'), 0, 1) == '0') echo substr($activity->duration->format('H'), 1, strlen($activity->duration->format('H')));
                        else echo $activity->duration->format('H') ?><span class="ac-spec-unit"> h </span><?= $activity->duration->format('i') ?></div>
                </div>
                <div class="ac-spec <?= $activity->setBackgroundColor('elevation')?> ">
                    <div class="ac-spec-label"><strong>Elevation : </strong></div>
                    <div class="ac-spec-value"><?= $activity->route->elevation ?><span class="ac-spec-unit"> m</span></div>
                </div>
                <div class="ac-spec <?= $activity->setBackgroundColor('speed')?> ">
                    <div class="ac-spec-label"><strong>Avg. Speed : </strong></div>
                    <div class="ac-spec-value"><?= $activity->getAverageSpeed() ?><span class="ac-spec-unit"> km/h</span></div>
                </div>
            </div>
        </div>

        <div class="ac-thumbnail-container">
            <a href="/activity/<?= $activity->id ?>">
                <img class="ac-map-thumbnail" src="<?= $activity->route->thumbnail ?>">
            </a>
        </div>

    </div>

    <div class="ac-photos-container"> <?php
        $i = 1;
        $preview_photos = $activity->getPreviewPhotos(PREVIEW_PHOTOS_QUANTITY);
        foreach ($preview_photos as $photo) { ?>
            <a href="/activity/<?= $activity->id ?>">
                <div class="ac-photo-container<?php if ($photo->featured) echo ' featured'; ?>">
                    <img class="ac-photo" src="<?= 'data:' . $photo->type . ';base64,' . $photo->blob ?>"> <?php
                    if ($i == PREVIEW_PHOTOS_QUANTITY AND count($activity->getPhotoIds()) > PREVIEW_PHOTOS_QUANTITY) { ?>
                        <div class="ac-photos-others"><div>+ <?= count($activity->getPhotoIds()) - PREVIEW_PHOTOS_QUANTITY + 1 ?></div></div> <?php
                    } ?>
                </div>
            </a> <?php
            $i++;
        } ?>
    </div>

</div>