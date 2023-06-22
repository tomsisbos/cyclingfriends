<?php

include '../actions/users/initSessionAction.php';
include '../actions/databaseAction.php';
include '../includes/head.php';

// Remove if necessary
if (isset($_POST['remove'])) {
    $ride = new Ride($_POST['ride_id']);
    $ride->removeGuide($_POST['guide_id']);
    $removed_guide = new Guide($_POST['guide_id']);
    $successmessage = $ride->name. 'のガイドを却下しました。';
} ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/ride.css" />

<body> <?php

	include '../includes/navbar.php';

    // Space for general error messages
    include '../includes/result-message.php'; ?>

	<div class="main container">
        
        <h2 class="py-3">ガイドを託されている場合、下記の表示されます。</h2><?php

        $getGuideRequests = $db->prepare("SELECT g.ride_id, g.position FROM ride_guides AS g JOIN rides AS r ON g.ride_id = r.id WHERE g.user_id = ? AND r.date > NOW()");
        $getGuideRequests->execute([$connected_user->id]);
        if ($getGuideRequests->rowCount() > 0) {
            while ($guide_data = $getGuideRequests->fetch(PDO::FETCH_ASSOC)) {
                $guide = new Guide($connected_user->id, $guide_data['ride_id'], $guide_data['position']); ?>
                <form method="POST" class="align-items-center d-flex gap-20 bg-white px-4 py-2">
                    <div><?= $guide->ride->date ?></div>
                    <a href="<?= $router->generate('ride-single', ['ride_id' => $guide->ride->id]) ?>"><strong><?= $guide->ride->name ?></a></strong>
                    <div><?= $guide->getPositionString() ?></div>
                    <input type="submit" class="btn smallbutton push" name="remove" value="却下" />
                    <input type="hidden" name="guide_id" value="<?= $guide->id ?>" />
                    <input type="hidden" name="ride_id" value="<?= $guide->ride->id ?>" />
                </form>
                <?php
            }
        } else echo 'ガイドリクエストは届いていません。' ?>

	</div>

</body>
</html>