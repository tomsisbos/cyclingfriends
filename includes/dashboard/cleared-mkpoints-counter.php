<?php

// Get total mkpoints number
$countMkpoints = $db->prepare("SELECT id FROM map_mkpoint");
$countMkpoints->execute();
$mkpoints_number = $countMkpoints->rowCount();

// Get user cleared mkpoints number
$cleared_mkpoints_number = $connected_user->countClearedMkpoints(); ?>

<div class="dashboard-title-block">
    最近訪れた絶景スポット <div class="cleared-mkpoint-counter">(<?= $cleared_mkpoints_number . ' / ' . $mkpoints_number ?>)</div>
</div>