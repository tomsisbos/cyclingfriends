<div class="rd-card" id="rd-card">
            
    <!-- Left container -->
    <div class="rd-top-container">
        <a href="<?= 'ride/' .$ride->id;?>" class="fullwidth">
            <?php // Truncate ride name if more than 60 characters
            $featuredImage = $ride->getFeaturedImage(); ?>
            <div class="rd-image" style="background-image: url(<?= $featuredImage->url; ?>); background-color: lightgrey">
                <div class="<?php if ($featuredImage){ echo 'rd-ride-title'; } else { echo 'rd-ride-name'; }?>"><?= $ride->name; ?></div>
                <div class="<?php if ($featuredImage){ echo 'rd-ride-date'; } else { echo 'rd-ride-date'; }?>"><?= $ride->date; ?></div>
            </div>
        </a> <?php
        if (isset($ride->route_id)) echo '<img class="rd-route-thumbnail" src="' . $ride->getRoute()->getThumbnail() . '">' ?>
    </div>

    <!-- Bottom container --> 
    <div class="rd-bottom-container">
        <div class="rd-details">
            <div class="rd-section-address">
                <span class="iconify" data-icon="gis:poi-map" data-width="20"></span>
                <div class="text">
                    <p><strong><?= $ride->meeting_place; ?></strong><?= ' - ' .$ride->meeting_time; ?></p>
                </div>
            </div>
            <div class="rd-section-text">
                <p><?= $ride->getAcceptedLevelTags(). ' (' .$ride->getAcceptedBikesString(). ')'; ?></p>
                <div class="rd-distance">
                    <p><strong>距離 : </strong><?php if ($ride->distance_about === 'about') { echo '約'; } echo $ride->distance. 'km'; ?></p>
                    <?= $ride->getTerrainIcon() ?>
                </div>
                <div class="rd-checkpoints"> <?php
                    if (isset($ride->checkpoints)) {
                        $i = 0;
                        foreach ($ride->checkpoints as $checkpoint) {
                        $i++ ?>
                        <div class="rd-checkpoint <?php if ($i < count($ride->checkpoints)) echo 'arrow' ?>">
                            <div class="rd-cpt-details">
                                <div class="rd-cpt-header">
                                    <div class="rd-cpt-number"><?= $checkpoint->number ?></div>
                                    <div class="rd-cpt-distance"><?php if ($checkpoint->distance > 0) echo 'km ' . round($checkpoint->distance, 1) ?></div>
                                </div>
                                <div class="rd-cpt-name"><?= $checkpoint->name ?></div>
                            </div>
                            <div class="rd-cpt-thumbnail"> <?php
                                if ($checkpoint->img->filename !== NULL) { ?>
                                    <img src="<?= $checkpoint->img->url ?>"> <?php
                                } else { ?>
                                    <img src="/media/default-photo-<?= rand(1, 9) ?>.svg"> <?php
                                } ?>
                            </div>
                        </div> <?php
                        }
                    } ?>
                </div>
            </div>
        </div>
        <div class="rd-section-organizer">
            <a href="<?= 'rider/' .$ride->author_id; ?>">
                <?= $ride->getAuthor()->getPropicElement(60, 60, 60); ?>
            </a>
            <div class="rd-organizer">
                <div class="rd-login"><?= 'by <strong>@' .$ride->getAuthor()->login. '</strong>'; ?></div> <?php
                if ($ride->privacy === 'Friends only') { ?>
                    <p style="background-color: #ff5555" class="tag-light text-light">友達のみ</p> <?php
                } ?>
            </div>
        </div>
        <div class="rd-section-entry" style="background-color: <?= $ride->getStatusColor(); ?>;">
            <span style="vertical-align: -webkit-baseline-middle;">
                <?= '<strong>Entry : </strong>' .$ride->status;
                if ($ride->entry_start > date('Y-m-d')) {
                    if (nbDaysLeftToDate($ride->entry_start) == 1) echo '<br><div class="xsmallfont">明日開始</div>';
                    else echo '<br><div class="xsmallfont">エントリー開始まで残り' .nbDaysLeftToDate($ride->entry_start). '日</div>';
                } else if ($ride->entry_start <= date('Y-m-d') AND date('Y-m-d') <= $ride->entry_end) {
                    if (nbDaysLeftToDate($ride->date) == 0) echo '<br><div class="xsmallfont">本日締切</div>';
                    else if (nbDaysLeftToDate($ride->date) == 1) echo '<br><div class="xsmallfont">明日締切</div>';
                    else echo '<br><div class="xsmallfont">締め切りまで残り' .nbDaysLeftToDate($ride->entry_end). '日</div>';
                } else if ($ride->entry_end <= date('Y-m-d') AND date('Y-m-d') <= $ride->date) {
                    if (nbDaysLeftToDate($ride->date) == 0) echo '<br><div class="xsmallfont text-danger">本日出発</div>';
                    else if (nbDaysLeftToDate($ride->date) == 1) echo '<br><div class="xsmallfont">明日出発</div>';
                    else echo '<br><div class="xsmallfont">出発まで残り' .nbDaysLeftToDate($ride->date). '日</div>';
                } ?>
            </span>
        </div>
    </div>

</div>