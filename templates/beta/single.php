<?php

include '../actions/users/initSessionAction.php';
include '../actions/beta/devNoteAction.php';
include '../includes/head.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/beta.css">

<body> <?php

    include '../includes/navbar.php';

    // Space for general error messages
	include '../includes/result-message.php'; ?>

	<div class="main">

        <!-- Content -->
        <div class="container dvnt-header">
            <div class="dvnt-row">
                <div class="dvnt-user"><a href="/rider/<?= $dev_note->user_id ?>"><?php $dev_note->getUser()->getPropicElement() ?></a></div>
                <div class="dvnt-column">
                    <div class="dvnt-login"><a href="/rider/<?= $dev_note->user_id ?>"><?= $dev_note->getUser()->login ?></a></div>
                    <div class="dvnt-time"><?= $dev_note->time ?></div>
                </div>
            </div>
            <div class="dvnt-column">
                <div class="dvnt-url"><strong>URL : </strong> <?= $dev_note->url ?></div>
                <div class="dvnt-browser"><strong>ブラウザー : </strong> <?= $dev_note->browser ?></div>
                <div class="dvnt-type"><strong>タイプ : </strong> <?= $dev_note->type ?></div>
            </div>
            <div class="dvnt-title"><?= $dev_note->title ?></div>
            <div class="dvnt-content"><?= $dev_note->content ?></div>
        </div>

        <!-- Chat -->
        <div class="container bg-white end"> <?php
            if (!empty($dev_note->chat)) { ?>
                <div class="dvnt-chat"> <?php
                    foreach ($dev_note->chat as $message) { ?>
                        <div class="dvnt-chat-message <?php if ($message->getUser()->hasModeratorRights()) { echo 'admin'; } ?>">
                            <div class="dvnt-user"><a href="/rider/<?= $dev_note->user_id ?>"><?= $message->getUser()->getPropicElement() ?></a></div>
                            <div class="dvnt-bubble">
                                <div class="dvnt-message"><?= $message->content ?></div>
                            </div>
                        </div> <?php
                    } ?>
                </div> <?php
            } else echo '<div class="error-block"><div class="error-message">開発チームから回答させて頂きます。暫くお待ちください。</div></div>' ?>
        </div>

        <!-- New message -->
        <div class="container">
            <form method="post" class="chat-msgbox form-floating">
                <textarea class="form-control" name="message"></textarea>
                <label class="form-label" for="floatingInput">コメントを書く...</label>
                <button type="submit" class="btn button btn button-primary" name="send">送信</button>
            </form>
        </div>
	
	</div>
	
</body>
</html>