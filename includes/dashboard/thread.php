<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/mkpoint.css"> <?php

define("THREAD_LIMIT", 6);
define("PREVIEW_PHOTOS_QUANTITY", 5); ?>

<div class="dashboard-thread-container" id="threadContainer" data-limit="<?= THREAD_LIMIT ?>" data-photosquantity="<?= PREVIEW_PHOTOS_QUANTITY ?>"> <?php

    forEach ($connected_user->getThread(0, THREAD_LIMIT) as $entry) {

        // Get activity card if entry type is activity
        if ($entry->type == 'activity') {
            $activity = $entry; ?>
            <div class="top-link"><a href="/activities">Activity</a></div> <?php
            include '../includes/activities/card.php';
        
        // Get mkpoint card if entry type is mkpoint
        } else if ($entry->type == 'mkpoint') { ?>
            <div class="top-link"><a href="/world">Scenery point</a></div> <?php
            $mkpoint = $entry;
            include '../includes/mkpoints/card.php';
        }

    } ?>

</div>