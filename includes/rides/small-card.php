<div class="my-rd-card">
    <a href="/ride/<?= $ride->id ?>">
        <div class="my-rd-thumbnail" style="background-image: url(<?= $ride->getFeaturedImage()->url; ?>);">
            <div class="my-rd-header header-block"> 
                <div class="my-rd-status tag-light <?= $ride->getStatusClass(); ?>">
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
        <div class="my-rd-entry-period"> <?php
            if (!empty($ride->entry_end)) echo '<strong>募集期間：</strong>' .$ride->entry_start. ' ∼ ' .$ride->entry_end;
            else echo '<strong>募集期間：</strong>未定'; ?>
        </div>
        <div class="append-buttons">
            <a href="/ride/<?= $ride->id ?>">
                <button class="mp-button normal">詳細</button>
            </a> <?php if ($connected_user->id == $ride->author_id) { ?>
                <a href="/ride/<?= $ride->id ?>/edit">
                    <button class="mp-button success">編集</button>
                </a>
                <a href="/ride/<?= $ride->id ?>/admin">
                    <button class="mp-button admin">管理</button>
                </a>
                <button class="mp-button danger js-delete-ride" data-id="<?= $ride->id ?>">削除</button> <?php
            } ?>
        </div>
    </div>
</div>

