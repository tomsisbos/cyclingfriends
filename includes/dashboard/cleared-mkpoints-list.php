<div class="dashboard-block cleared-mkpoints-list"> <?php

    $cleared_mkpoints = $connected_user->getClearedMkpoints(CLEARED_MKPOINTS_LIMIT); 

    // If no cleared mkpoint yet, display an error message
    if (count($cleared_mkpoints) == 0) { ?>
        <div class="success-block">
            <div class="success-message">
                You don't have any cleared scenery point yet. Let's explore the world together !
            </div>
        </div> <?php

    // Else, display cleared mkpoints table
    } else { ?>
        <div class="cleared-mkpoints"> <?php
            foreach ($cleared_mkpoints as $entry) {
                $cleared_mkpoint = new Mkpoint ($entry['mkpoint_id']);
                $cleared_mkpoint->activity = new Activity ($entry['activity_id']);
                ///var_dump($cleared_mkpoint); ?>
                <div class="cleared-mkpoint">
                    <div class="cleared-mkpoint-thumbnail"><img src="data:image/jpeg;base64,<?= $cleared_mkpoint->thumbnail ?>"></div>
                    <div class="cleared-mkpoint-activity-date"><?= $cleared_mkpoint->activity->datetime->format('Y/m/d'); ?></div>
                    <div class="cleared-mkpoint-name"><?= $cleared_mkpoint->name ?></div>
                    <div class="cleared-mkpoint-place"><?= $cleared_mkpoint->prefecture . ', ' . $cleared_mkpoint->city ?></div>
                    <div class="cleared-mkpoint-activity-title">(<a target="_blank" href="/activity/<?= $cleared_mkpoint->activity->id ?>"><?= $cleared_mkpoint->activity->title ?></a>)</div>
                </div> <?php
            } ?>
        </div> <?php
    } ?>

</div>