<link rel="stylesheet" href="/assets/css/activity.css">
<link rel="stylesheet" href="/assets/css/mkpoint.css"> <?php

define("PREVIEW_PHOTOS_QUANTITY", 5); ?>

<div class="dashboard-thread-container" id="threadContainer" data-limit="<?= 20 ?>" data-photosquantity="<?= PREVIEW_PHOTOS_QUANTITY ?>"> <?php

    forEach ($connected_user->getThread() as $entry) {
        // Get activity card if entry type is activity
        if ($entry->type == 'activity') {
            $activity = $entry;
            if ($activity->hasAccess($connected_user)) {
                include 'includes/activities/card.php';
            }
        // Get mkpoint card if entry type is mkpoint
        } else if ($entry->type == 'mkpoint') {
            $mkpoint = $entry;
            include 'includes/mkpoints/card.php';
        }
    } ?>

</div>