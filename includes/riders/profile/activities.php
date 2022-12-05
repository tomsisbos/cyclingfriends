<div class="profile-title-block">
    <h2>Latest activities</h2><div class="cleared-counter"><?= '(' . $user->getActivitiesNumber() . ')' ?></div>
</div>

<div class="pf-activities-list dashboard-block"> <?php
    $activities = $user->getActivities(0, 6);
    foreach ($activities as $activity) {
        $activity = new Activity($activity['id']);
        $featured_image = $activity->getFeaturedImage() ?>
        <a class="pf-activity-container" href="/activity/<?= $activity->id ?>">
            <div class="pf-activity-photo"> <?php
                if ($featured_image) { ?>
                    <img src="data:<?= $featured_image->type ?>;base64,<?= $featured_image->blob ?>" /> <?php
                } else { ?>
                    <img src="/media/default-photo-<?= rand(0, 9) ?>.svg" /> <?php
                } ?>
            </div>
            <div class="pf-activity-infos">
                <div class="pf-activity-title"><?= $activity->title ?></div> - <div class="pf-activity-datetime"><?= $activity->datetime->format('Y/m/d') ?></div>
                <div class="pf-activity-line"><div class="pf-activity-distance">Distance : <?= $activity->route->distance ?>km</div><div class="pf-activity-duration">Duration : <?= $activity->duration->format('H\hi') ?></div></div>
                <div class="pf-activity-story"><?= $activity->getFirstStory() ?></div>
            </div>
        </a> <?php
    } ?>
</div>