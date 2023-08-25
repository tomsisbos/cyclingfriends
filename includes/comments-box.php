<?php

foreach ($object->getComments() as $comment) { ?>
    <div class="chat-line<?php if ($comment->user->id == $object->user_id) echo ' py-2 bg-admin'?>">
        <?= $comment->user->getPropicElement(40, 40) ?>
        <div class="chat-message-block" style="margin-left: 10px;">
            <a href="/rider/<?= $comment->user->id ?>" target="_blank">
                <div class="chat-login"><?= $comment->user->login ?></div>
            </a>
            <div class="chat-time"><?= $comment->time ?></div>
            <div class="chat-message"><?= $comment->content ?></div>
        </div>
    </div> <?php
} ?>

<form method="POST" class="chat-msgbox">
    <textarea name="content" class="fullwidth" placeholder="コメントを記入..."></textarea>
    <input type="submit" name="comment" class="btn button text-white text-wrap" value="投稿" />
</form>