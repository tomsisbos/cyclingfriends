<?php

// Get total mkpoints number
require '../actions/databaseAction.php';
$countMkpoints = $db->prepare("SELECT id FROM map_mkpoint");
$countMkpoints->execute();
$mkpoints_number = $countMkpoints->rowCount();

// Get user cleared mkpoints number
$cleared_mkpoints_number = $user->countClearedMkpoints(); ?>

<div class="profile-title-block">
    <h2>Recently cleared scenery spots</h2> <div class="cleared-counter">(<?= $cleared_mkpoints_number . ' / ' . $mkpoints_number ?>)</div>
</div>

<div class="dashboard-block cleared-mkpoints-list"> <?php

    $cleared_mkpoints = $user->getClearedMkpoints(CLEARED_MKPOINTS_LIMIT); 

    // If no cleared mkpoint yet, display an error message
    if (count($cleared_mkpoints) == 0) { ?>
        <div class="success-block">
            <div class="success-message">
                <?= $user->login ?> don't have any cleared scenery point yet. Let's explore the world together !
            </div>
        </div> <?php

    // Else, display cleared mkpoints table
    } else { ?>
        <div class="cleared-mkpoints"> <?php
            foreach ($cleared_mkpoints as $entry) {
                $cleared_mkpoint = new Mkpoint ($entry['mkpoint_id']);
                $cleared_mkpoint->activity = new Activity ($entry['activity_id']);
                ///var_dump($cleared_mkpoint); ?>
                <div class="cleared-mkpoint">
                    <div class="cleared-mkpoint-thumbnail"><img src="data:image/jpeg;base64,<?= $cleared_mkpoint->thumbnail ?>"></div>
                    <div class="cleared-mkpoint-activity-date"><?= $cleared_mkpoint->activity->datetime->format('Y/m/d'); ?></div>
                    <div class="cleared-mkpoint-name"><?= $cleared_mkpoint->name ?></div>
                    <div class="cleared-mkpoint-place"><?= $cleared_mkpoint->prefecture . ', ' . $cleared_mkpoint->city ?></div>
                    <div class="cleared-mkpoint-activity-title">(<a target="_blank" href="/activity/<?= $cleared_mkpoint->activity->id ?>"><?= $cleared_mkpoint->activity->title ?></a>)</div>
                </div> <?php
            } ?>
        </div> <?php
    } ?>

</div> <?php

// Get cleared mkpoints location
$getClearedMkpointsData = $db->prepare("SELECT name, city, prefecture FROM map_mkpoint INNER JOIN user_mkpoints ON map_mkpoint.id = user_mkpoints.mkpoint_id WHERE user_mkpoints.user_id = ? GROUP BY user_mkpoints.mkpoint_id");
$getClearedMkpointsData->execute(array($user->id));
$mkpoints_data = $getClearedMkpointsData->fetchAll(PDO::FETCH_ASSOC);

// Get number of cleared mkpoints per prefecture and city
$locations = [];
foreach ($mkpoints_data as $entry) {
    if (!in_array_key_r($entry['prefecture'], $locations)) $locations[$entry['prefecture']] = ['number' => 1, 'cities' => [$entry['city'] => 1]];
    else {
        $locations[$entry['prefecture']]['number']++;
        if (!isset($locations[$entry['prefecture']]['cities'][$entry['city']])) $locations[$entry['prefecture']]['cities'][$entry['city']] = 1;
        else $locations[$entry['prefecture']]['cities'][$entry['city']]++;
    }
}

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

<script src="/scripts/riders/cleared-mkpoints-stats.js"></script>