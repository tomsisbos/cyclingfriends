<div class="mk-card" data-id="<?= $mkpoint->id ?>">
    <div class="mk-photo"><a href="/scenery/<?= $mkpoint->id ?>"><img src="<?= $mkpoint->getImages()[0]->url ?>"></a></div>
    <div class="mk-data">
        <div class="mk-top"> 
            <a href="/rider/<?= $mkpoint->user_id ?>"><?php
                $user = new User($mkpoint->user_id);
                $user->getPropicElement(); ?>
            </a>
            <div class="mk-top-text">
                <a href="/scenery/<?= $mkpoint->id ?>"><div class="mk-title"><?= $mkpoint->name ?></div></a> <?php
                $cleared_activity_id = $mkpoint->isCleared();
                if ($cleared_activity_id) { ?>
                    <div id="visited-icon" style="display: inline;" title="この絶景スポットを訪れました。">
                        <a href="/activity/<?= $cleared_activity_id ?>" target="_blank">
                            <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                        </a>
                    </div> <?php
                } ?>
                <div class="mk-place-elevation"><?= $mkpoint->city . ' (' . $mkpoint->prefecture . ') - ' . $mkpoint->elevation . 'm' ?></div>
            </div>
        </div>
        <div class="mk-description"><?= $mkpoint->description ?></div>
    </div>
</div>