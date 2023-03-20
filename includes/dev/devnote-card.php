<a href="/dev/note/<?= $dev_note->id ?>">
    <div class="dvnt-board-item<?php
        if ($dev_note->isAnswered()) echo ' answered';
        else if ($dev_note->getUser()->hasModeratorRights()) echo ' bg-admin' ?>
    ">
        <div class="dvnt-board-user">
            <div class="dvnt-board-propic"> <?php
                $dev_note->getUser()->getPropicElement(); ?>
            </div>
            <div class="dvnt-board-login">
                <?= $dev_note->getUser()->login; ?>
            </div>
        </div>
        <div class="dvnt-board-specs">
            <?= $dev_note->time .' - '. $dev_note->type; ?>
        </div>
        <div class="dvnt-board-title">
            <?= $dev_note->title; ?>
        </div>
    </div>
</a>