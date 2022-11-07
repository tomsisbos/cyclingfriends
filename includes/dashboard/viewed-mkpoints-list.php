<?php

$viewed_mkpoints = $connected_user->getViewedMkpoints(VIEWED_MKPOINTS_LIMIT); 

// If no viewed mkpoint yet, display an error message
if (count($viewed_mkpoints) == 0) { ?>
    <div class="success-block">
        <div class="success-message">
            You don't have any viewed scenery point yet. Let's explore the world together !
        </div>
    </div> <?php

// Else, display viewed mkpoints table
} else { ?>
    <div class="viewed-mkpoints"> <?php
        foreach ($viewed_mkpoints as $entry) {
            $viewed_mkpoint = new Mkpoint ($entry['mkpoint_id']);
            $viewed_mkpoint->activity = new Activity ($entry['activity_id']);
            ///var_dump($viewed_mkpoint); ?>
            <div class="viewed-mkpoint">
                <div class="viewed-mkpoint-thumbnail"><img src="data:image/jpeg;base64,<?= $viewed_mkpoint->thumbnail ?>"></div>
                <div class="viewed-mkpoint-activity-date"><?= $viewed_mkpoint->activity->datetime->format('Y/m/d'); ?></div>
                <div class="viewed-mkpoint-name"><?= $viewed_mkpoint->name ?></div>
                <div class="viewed-mkpoint-place"><?= $viewed_mkpoint->prefecture . ', ' . $viewed_mkpoint->city ?></div>
                <div class="viewed-mkpoint-activity-title">(<a target="_blank" href="/activity.php?id=<?= $viewed_mkpoint->activity->id ?>"><?= $viewed_mkpoint->activity->title ?></a>)</div>
            </div> <?php
        } ?>
    </div> <?php
}

?>