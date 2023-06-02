<?php
if (isset($_POST['backfill'])) {
    $garmin = $connected_user->getGarmin();
    if ($garmin->isUserConnected()) $garmin->backfill();
}