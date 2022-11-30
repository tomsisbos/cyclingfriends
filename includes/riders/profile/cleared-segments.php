<?php

// Get total segments number
require '../actions/databaseAction.php';
$countSegments = $db->prepare("SELECT id FROM segments");
$countSegments->execute();
$segments_number = $countSegments->rowCount();

// Get user cleared segments number
$cleared_segments_number = $user->countClearedSegments(); ?>

<div class="profile-title-block">
    <h2>Recently cleared scenery spots</h2> <div class="cleared-segment-counter">(<?= $cleared_segments_number . ' / ' . $segments_number ?>)</div>
</div>

<div class="dashboard-block cleared-segments-list"> <?php

    $cleared_segments = $user->getClearedSegments(CLEARED_SEGMENTS_LIMIT); 

    // If no cleared segment yet, display an error message
    if (count($cleared_segments) == 0) { ?>
        <div class="success-block">
            <div class="success-message">
                <?= $user->login ?> don't have any cleared segment yet. Let's explore the world together !
            </div>
        </div> <?php

    // Else, display cleared segments table
    } else { ?>
        <div class="cleared-segments"> <?php
            foreach ($cleared_segments as $entry) {
                $cleared_segment = new Segment ($entry['segment_id']);
                $cleared_segment->activity = new Activity ($entry['activity_id']); ?>
                <div class="cleared-segment">
                    <div class="cleared-segment-thumbnail"><img src="<?= $cleared_segment->getFeaturedImage() ?>"></div>
                    <div class="cleared-segment-activity-date"><?= $cleared_segment->activity->datetime->format('Y/m/d'); ?></div>
                    <div class="cleared-segment-name"><?= $cleared_segment->name ?></div>
                    <div class="cleared-segment-place">From <?= $cleared_segment->route->startplace ?> to <?= $cleared_segment->route->goalplace ?></div>
                    <div class="cleared-segment-activity-title">(<a target="_blank" href="/activity/<?= $cleared_segment->activity->id ?>"><?= $cleared_segment->activity->title ?></a>)</div>
                </div> <?php
            } ?>
        </div> <?php
    } ?>

</div> <?php

// Get cleared segments location
$getClearedSegmentsData = $db->prepare("SELECT startplace FROM routes INNER JOIN segments ON routes.id = segments.route_id INNER JOIN user_segments ON segments.id = user_segments.segment_id WHERE user_segments.user_id = ?");
$getClearedSegmentsData->execute(array($user->id));
$segments_data = $getClearedSegmentsData->fetchAll(PDO::FETCH_ASSOC);

// Get number of cleared segments per prefecture and city
$locations = [];
foreach ($segments_data as $entry) { var_dump($entry);/* /// Treat location string
    if (!in_array_key_r($entry['prefecture'], $locations)) $locations[$entry['prefecture']] = ['number' => 1, 'cities' => [$entry['city'] => 1]];
    else {
        $locations[$entry['prefecture']]['number']++;
        if (!isset($locations[$entry['prefecture']]['cities'][$entry['city']])) $locations[$entry['prefecture']]['cities'][$entry['city']] = 1;
        else $locations[$entry['prefecture']]['cities'][$entry['city']]++;
    }*/
}/*

// Get total number of mkpoints for each prefecture found and write it
$prefectures_total = [];
$cities_total = []; ?>
<div class="cleared-mkpoint-block"> <?php
    foreach ($locations as $prefecture => $data) {
        $getPrefectureTotalMkpoints = $db->prepare("SELECT id FROM map_mkpoint WHERE prefecture = ?");
        $getPrefectureTotalMkpoints->execute(array($prefecture));
        $prefectures_total[$prefecture] = $getPrefectureTotalMkpoints->rowCount();
        
        // Build prefecture block elements ?>
        <div class="cleared-mkpoint-prefecture-container">
            <div class="cleared-mkpoint-prefecture-block"> 
                <div class="dropdown-toggle"><strong><?= $prefecture ?></strong> : <?= $locations[$prefecture]['number'] ?> / <?= $prefectures_total[$prefecture] ?></div>
            </div>
            <div class="cleared-mkpoint-city-container hidden"> <?php
                // Build cities block elements
                foreach ($data['cities'] as $city => $number) {
                    $getCityTotalMkpoints = $db->prepare("SELECT id FROM map_mkpoint WHERE city = ?");
                    $getCityTotalMkpoints->execute(array($city));
                    $cities_total[$city] = $getCityTotalMkpoints->rowCount(); ?>
                    <div class="cleared-mkpoint-city-block"> 
                        <?= $city ?> : <?= $number ?> / <?= $cities_total[$city]; ?>
                    </div> <?php
                } ?>
            </div>
        </div> <?php
    } ?>
</div>

<script src="/scripts/riders/cleared-segments-stats.js"></script>*/