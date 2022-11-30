<?php

require '../actions/databaseAction.php';

// Get cleared mkpoints location
$getClearedMkpointsData = $db->prepare("SELECT city, prefecture FROM map_mkpoint INNER JOIN user_mkpoints ON map_mkpoint.id = user_mkpoints.mkpoint_id WHERE user_mkpoints.user_id = ?");
$getClearedMkpointsData->execute(array($connected_user->id));
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

<script src="/scripts/dashboard/cleared-mkpoints-stats.js"></script>