<div class="pg-ac-timeline"></div>

<div class="pg-ac-checkpoints-container"> <?php
    $photo_number = 1;
    foreach ($activity->getCheckpoints() as $checkpoint) { ?>
        <div class="pg-ac-checkpoint-container" id="checkpoint<?= $checkpoint->number ?>" data-number="<?= $checkpoint->number ?>">
            <div class="pg-ac-photos-container"> <?php
                foreach ($checkpoint->getPhotos() as $photo) {
                    // Only add photos which privacy is not set to true, except for the author
                    if ($photo->privacy != 'private' || (isset($_SESSION['auth']) && $activity->user_id == getConnectedUser()->id)) { ?>
                        <div class="pg-ac-photo-container">
                            <div class="pg-ac-photo-specs">
                                <div class="pg-ac-photo-number"><?= $photo_number ?></div>
                                <div class="pg-ac-photo-distance"></div>
                            </div>
                            <img class="pg-ac-photo" data-id="<?= $photo->id ?>" src="<?= $photo->url ?>" />
                        </div> <?php
                        $photo_number++;
                    }
                } ?>
            </div>
            <div class="pg-ac-checkpoint-topline">
                <?= $checkpoint->getIcon() . ' km ' . round($checkpoint->distance, 1); ?>
                <span class="pg-ac-checkpoint-time"> <?php
                    $time = $checkpoint->datetime->diff($activity->getCheckpoints()[0]->datetime);
                    if ($time->h != 0 AND $time->i != 0) {
                        echo ' (';
                        if ($time->h > 0) {
                            if (substr($time->h, 0, 1) == '0') echo substr($time->h, 1, strlen($time->h)) . 'h';
                            else echo $time->h . 'h';
                            if (strlen($time->i) == 1) echo '0' . $time->i;
                            else echo $time->i;
                        } else {
                            echo $time->i . ' min'; 
                        }
                        echo ') ';
                    } ?>
                </span>
                <?= ' - ' . $checkpoint->name ?>
            </div>
            <div class="pg-ac-checkpoint-story">
                <?= $checkpoint->story ?>
            </div>
        </div> <?php
    } ?>
</div>