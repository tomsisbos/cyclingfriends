<?php 

$getRides = $db->prepare('SELECT id FROM rides
WHERE 
    privacy != "private" AND	
    (CASE 
        WHEN :level = "Beginner" THEN level_beginner 
        WHEN :level = "Intermediate" THEN level_intermediate
        WHEN :level = "Athlete" THEN level_athlete 
        ELSE true 
    END) = TRUE 
    AND status LIKE "Open%"
ORDER BY
    date, meeting_time ASC
LIMIT 3');
$getRides->execute(array(":level" => $connected_user->level));

// Display ride cards ?>
<div class="dashboard-title-block">
    Next rides
</div>

<div class="dashboard-cards"> <?php
    while ($data = $getRides->fetch(PDO::FETCH_ASSOC)) {
        $ride = new Ride($data['id']);
        $featured_image = $ride->getFeaturedImage(); ?>
        <div class="rd-card">
            <a href="/rides/ride.php?id=<?= $ride->id ?>">
                <div class="rd-header" style="background-image: url(data:image/jpeg;base64,<?= $featured_image['img']; ?>); background-color: lightgrey">
                    <div class="rd-title"><?= $ride->name ?></div>
                    <div class="rd-date"><?= $ride->date ?></div>
                </div>
            </a>
            <div class="rd-details">
                <div class="rd-course"><?= $ride->meeting_place ?> - <?php if ($ride->distance_about === 'about') { echo $ride->distance_about. ' '; } echo $ride->distance. 'km';
                    if ($ride->terrain == 1) echo '<img src="\includes\media\flat.svg" />';
                    else if ($ride->terrain == 2) echo '<img src="\includes\media\smallhills.svg" />';
                    else if ($ride->terrain == 3) echo '<img src="\includes\media\hills.svg" />';
                    else if ($ride->terrain == 4) echo '<img src="\includes\media\mountain.svg" />'; ?>
                </div>
                <div><?= $ride->getAcceptedLevelTags() ?></div>
                <div class="rd-organizer">by @<a target="_blank" href="/riders/profile.php?id=<?= $ride->author->id ?>"><?= $ride->author->login ?></a></div>
            </div>
            <div class="rd-entry"></div>
        </div> <?php
    } ?>
</div>