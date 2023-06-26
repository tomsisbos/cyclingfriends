<?php 

$rides_date_range = 3; // Time range of next rides to display (in months)

$getRides = $db->prepare('SELECT id FROM rides
WHERE 
    privacy != "private" AND	
    (CASE 
        WHEN :level = "Beginner" THEN level_beginner 
        WHEN :level = "Intermediate" THEN level_intermediate
        WHEN :level = "Athlete" THEN level_athlete 
        ELSE true 
    END) = TRUE
	AND date BETWEEN :today AND :datemax 
ORDER BY
    date, meeting_time ASC
LIMIT 3');
$today = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
$getRides->execute(array(":level" => getConnectedUser()->level, ":today" => $today->format('Y-m-d H:i:s'), ":datemax" => $today->modify('+' . $rides_date_range . ' month')->format('Y-m-d H:i:s')));

// Display ride cards ?>
<div class="dashboard-title-block">
    募集中のライド
</div>

<div class="dashboard-cards"> <?php
    while ($data = $getRides->fetch(PDO::FETCH_ASSOC)) {
        $ride = new Ride($data['id']);
        $featured_image = $ride->getFeaturedImage(); ?>
        <div class="rd-card">
            <a href="/ride/<?= $ride->id ?>">
                <div class="rd-header" style="background-image: url(data:image/jpeg;base64,<?= $featured_image['img']; ?>); background-color: lightgrey">
                    <div class="rd-title"><?= $ride->name ?></div>
                    <div class="rd-date"><?= $ride->date ?></div>
                </div>
            </a>
            <div class="rd-details">
                <div class="rd-course"><?= $ride->meeting_place ?> - <?php if ($ride->distance_about === 'about') { echo $ride->distance_about. ' '; } echo $ride->distance. 'km';
                    if ($ride->terrain == 1) echo '<img src="\media\flat.svg" />';
                    else if ($ride->terrain == 2) echo '<img src="\media\smallhills.svg" />';
                    else if ($ride->terrain == 3) echo '<img src="\media\hills.svg" />';
                    else if ($ride->terrain == 4) echo '<img src="\media\mountain.svg" />'; ?>
                </div>
                <div><?= $ride->getAcceptedLevelTags() ?></div>
                <div class="rd-organizer">by @<a target="_blank" href="/rider/<?= $ride->author->id ?>"><?= $ride->author->login ?></a></div>
            </div>
            <div class="rd-entry"></div>
        </div> <?php
    } ?>
</div>