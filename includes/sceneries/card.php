<div class="mk-card" data-id="<?= $scenery->id ?>">
    <div class="mk-photo"><a href="/scenery/<?= $scenery->id ?>"><img src="<?= $scenery->getImages()[0]->url ?>"></a></div>
    <div class="mk-data">
        <div class="mk-top"> 
            <a href="/rider/<?= $scenery->user_id ?>"><?php
                $user = new User($scenery->user_id);
                $user->getPropicElement(); ?>
            </a>
            <div class="mk-top-text">
                <a href="/scenery/<?= $scenery->id ?>"><div class="mk-title"><?= $scenery->name ?></div></a> <?php
                $cleared_activity_id = $scenery->isCleared();
                if ($cleared_activity_id) { ?>
                    <div id="visited-icon" style="display: inline;" title="この絶景スポットを訪れました。">
                        <a href="/activity/<?= $cleared_activity_id ?>" target="_blank">
                            <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                        </a>
                    </div> <?php
                } ?>
                <div class="mk-place-elevation"><?= $scenery->city . ' (' . $scenery->prefecture . ') - ' . $scenery->elevation . 'm' ?></div>
            </div>
        </div>
        <div class="mk-description"><?= $scenery->description ?></div>
    </div>
</div>