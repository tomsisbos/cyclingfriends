<link rel="stylesheet" href="/assets/css/activity.css"> 

<?php

// Define offset and number of articles to query
define("PREVIEW_PHOTOS_QUANTITY", 5);
$limit = 5;
$offset = 0; ?>

<div class="dashboard-main-cards" id="cardsContainer" data-limit="<?= $limit ?>" data-photosquantity="<?= PREVIEW_PHOTOS_QUANTITY ?>"> <?php

    forEach ($connected_user->getPublicActivities($offset, $limit) as $activity) {
        $activity = new Activity($activity['id']);
        if ($activity->hasAccess($connected_user)) {
            include 'includes/activities/card.php';
        }
    } ?>

</div>