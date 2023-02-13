<div class="my-rd-card">
    <a href="/ride/<?= $ride->id ?>">
        <div class="my-rd-thumbnail" style="background-image: url(<?= $ride->getFeaturedImage()->url; ?>);">
            <div class="my-rd-header header-block"> <?php
                // Set text color depending on the status
                $status_color = $ride->getStatusColor(); ?>
                <div class="my-rd-status tag-light" style="background-color: <?= $status_color ?>;">
                    <?= $ride->status;
                    // Only add substatus if there is one
                    if (!empty($ride->substatus)) { echo ' (' .$ride->substatus. ')'; } ?>
                </div> 
                <div class="header-row">
                    <h2><?= $ride->name ?></h2>
                </div>
                <p class="header-row"><?= $ride->date ?></p>
            </div>
        </div>
    </a>
    <div class="my-rd-details">
        <div class="my-rd-date">
            <strong>作成日：</strong><?= $ride->posting_date ?>
        </div> <?php
        // Get participation infos
        $participation = $ride->setParticipationInfos() ?>
        <div class="my-rd-participation">
            <strong>参加者：</strong><?= '<span style="color:' .$participation['participation_color']. '">' .$participation['participants_number']. '</span>&nbsp;/&nbsp;' .$ride->nb_riders_max. ' (min. ' .$ride->nb_riders_min. ')'; ?>
        </div>
        <div class="my-rd-entry-period">
            <strong>募集期間：</strong><?= $ride->entry_start ?> から <?= $ride->entry_end ?> まで
        </div>
        <div class="append-buttons">
            <a href="/ride/<?= $ride->id ?>">
                <div class="mp-button normal">詳細</div>
            </a> <?php if ($connected_user->id == $ride->author_id) { ?>
                <a href="/ride/<?= $ride->id ?>/edit">
                    <div class="mp-button success">編集</div>
                </a>
                <div class="mp-button danger js-delete-ride" data-id="<?= $ride->id ?>">削除</div> <?php
            } ?>
        </div>
    </div>
</div>

