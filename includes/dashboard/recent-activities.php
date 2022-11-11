<?php

// Define offset and number of articles to query
define("PREVIEW_PHOTOS_QUANTITY", 5);
$limit = 5;
$offset = 0; ?>

<div class="dashboard-main-cards" id="recentActivities" data-limit="<?= $limit ?>" data-photosquantity="<?= PREVIEW_PHOTOS_QUANTITY ?>"> <?php
    include 'includes/activities/list.php'; ?>
</div>