<?php

// Define offset and number of articles to query
define("PREVIEW_PHOTOS_QUANTITY", 5);
$limit = 6;
if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
else $offset = 0; ?>

<div class="dashboard-main-cards"> <?php
    include 'includes/activities/list.php'; ?>
</div>