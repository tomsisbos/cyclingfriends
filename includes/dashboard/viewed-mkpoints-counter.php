<?php

// Get total mkpoints number
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
$countMkpoints = $db->prepare("SELECT id FROM map_mkpoint");
$countMkpoints->execute();
$mkpoints_number = $countMkpoints->rowCount();

// Get user viewed mkpoints number
$viewed_mkpoints = $connected_user->getViewedMkpoints();
$viewed_mkpoints_number = count($viewed_mkpoints); ?>

<div class="dashboard-title-block">
    Recently viewed scenery spots <div class="viewed-mkpoint-counter">(<?= $viewed_mkpoints_number . ' / ' . $mkpoints_number ?>)</div>
</div>