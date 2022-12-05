<?php

// Get total mkpoints number
require '../actions/databaseAction.php';
$countMkpoints = $db->prepare("SELECT id FROM map_mkpoint");
$countMkpoints->execute();
$mkpoints_number = $countMkpoints->rowCount();

// Get user cleared mkpoints number
$cleared_mkpoints_number = $connected_user->countClearedMkpoints(); ?>

<div class="dashboard-title-block">
    Recently cleared scenery spots <div class="cleared-mkpoint-counter">(<?= $cleared_mkpoints_number . ' / ' . $mkpoints_number ?>)</div>
</div>