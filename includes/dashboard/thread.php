<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/scenery.css"> <?php

define("THREAD_LIMIT", 12);
define("PREVIEW_PHOTOS_QUANTITY", 5); ?>

<div class="dashboard-thread-container" id="threadContainer" data-limit="<?= THREAD_LIMIT ?>" data-photosquantity="<?= PREVIEW_PHOTOS_QUANTITY ?>"> <?php

    forEach (getConnectedUser()->getThread(0, THREAD_LIMIT) as $entry) { 

        // Get activity card if entry type is activity
        if ($entry['type'] == 'activity') {
            $activity = new Activity($entry['id']); ?>
            <div class="dashboard-card activity"> 
                <div class="top-link"><a href="/activities">アクティビティ</a></div> <?php
                include '../includes/activities/card.php'; ?>
            </div> <?php
        
        // Get scenery card if entry type is scenery
        } else if ($entry['type'] == 'scenery') {
            $scenery = new Scenery($entry['id']);
            $scenery->cleared = $scenery->isCleared(); ?>
            <div class="dashboard-card scenery"> 
                <div class="top-link"><a href="/world">絶景スポット</a></div> <?php
                include '../includes/sceneries/card.php'; ?>
            </div> <?php
        }

    } ?>

</div>

<div class="js-loader-container"></div>