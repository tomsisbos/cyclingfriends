<div class="rd-cards"> <?php

    if ($getRides->rowCount() > 0) {
        while ($ride = $getRides->fetch()) {

            $ride = new Ride ($ride['id']);
            
            // Only display rides accepting bike types matching connected user's registered bikes
            if (!(isset($_POST['filter_bike']) AND !$connected_user->checkIfAcceptedBikesMatches($ride))) {
            
                // Only display 'Friends only' rides if connected user is on the ride author's friends list
                if ($ride->privacy != 'Friends only' OR ($ride->privacy == 'Friends only' AND ($ride->author->id == $connected_user->id OR $ride->author->isFriend($connected_user)))) {

                    $is_ride = true; // Set "is_ride" variable to true as long as one ride to display has been found ?>
            
                    <div class="rd-card" id="rd-card">
            
                        <!-- Left container -->
                        <div class="rd-top-container">
                            <a href="<?= 'ride/' .$ride->id;?>" class="fullwidth">
                                <?php // Truncate ride name if more than 60 characters
                                $ride_name_truncated = truncate($ride->name, 0, 25);
                                $featuredImage = $ride->getFeaturedImage(); ?>
                                <div class="rd-image" style="background-image: url(data:image/jpeg;base64,<?= $featuredImage['img']; ?>); background-color: lightgrey">
                                    <div class="<?php if ($featuredImage){ echo 'rd-ride-title'; } else { echo 'rd-ride-name'; }?>"><?= $ride_name_truncated; ?></div>
                                    <div class="<?php if ($featuredImage){ echo 'rd-ride-date'; } else { echo 'rd-ride-date'; }?>"><?= $ride->date; ?></div>
                                </div>
                            </a> <?php
                            if (isset($ride->route)) echo '<img class="rd-route-thumbnail" src="' . $ride->route->getThumbnail() . '">' ?>
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
                                        <p><strong>距離 : </strong><?php if ($ride->distance_about === 'about') { echo $ride->distance_about. ' '; } echo $ride->distance. 'km'; ?></p> <?php
                                        if ($ride->terrain == 1) echo '<img src="\media\flat.svg" />';
                                        else if ($ride->terrain == 2) echo '<img src="\media\smallhills.svg" />';
                                        else if ($ride->terrain == 3) echo '<img src="\media\hills.svg" />';
                                        else if ($ride->terrain == 4) echo '<img src="\media\mountain.svg" />'; ?>
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
                                                    if ($checkpoint->img->blob !== null) { ?>
                                                        <img src="data:<?= $checkpoint->img->type ?>;base64,<?= $checkpoint->img->blob ?>"> <?php
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
                                <a href="<?= 'rider/' .$ride->author->id; ?>">
                                    <?= $ride->author->displayPropic(60, 60, 60); ?>
                                </a>
                                <div class="rd-organizer">
                                    <div class="rd-login"><?= 'by <strong>@' .$ride->author->login. '</strong>'; ?></div> <?php
                                    if ($ride->privacy === 'Friends only') { ?>
                                        <p style="background-color: #ff5555" class="tag-light text-light">友達のみ</p> <?php
                                    } ?>
                                </div>
                            </div>
                            <div class="rd-section-entry" style="background-color: <?= colorStatus($ride->status)[0]; ?>;">
                                <span style="vertical-align: -webkit-baseline-middle;">
                                    <?= '<strong>Entry : </strong>' .$ride->status;
                                    if ($ride->entry_start > date('Y-m-d')) {
                                        if (nbDaysLeftToDate($ride->entry_start) == 1) echo '<br><div class="xsmallfont">Opening tomorrow</div>';
                                        else echo '<br><div class="xsmallfont">エントリー開始まで残り' .nbDaysLeftToDate($ride->entry_start). '日</div>';
                                    } else if ($ride->entry_start <= date('Y-m-d') AND date('Y-m-d') <= $ride->entry_end) {
                                        if (nbDaysLeftToDate($ride->date) == 0) echo '<br><div class="xsmallfont">本日締め切り</div>';
                                        else if (nbDaysLeftToDate($ride->date) == 1) echo '<br><div class="xsmallfont">Entries ending tomorrow</div>';
                                        else echo '<br><div class="xsmallfont">締め切りまで残り' .nbDaysLeftToDate($ride->entry_end). '日</div>';
                                    } else if ($ride->entry_end <= date('Y-m-d') AND date('Y-m-d') <= $ride->date) {
                                        if (nbDaysLeftToDate($ride->date) == 0) echo '<br><div class="xsmallfont text-danger">本日出発</div>';
                                        else if (nbDaysLeftToDate($ride->date) == 1) echo '<br><div class="xsmallfont">明日出発</div>';
                                        else echo '<br><div class="xsmallfont">出発まで残り' .nbDaysLeftToDate($ride->date). '日</div>';
                                    } ?>
                                </span>
                            </div>
                        </div>
                    
                    </div> <?php
                }
            }
        
        }
    } ?>

</div>

<script src="/scripts/rides/display-rides.js"></script>