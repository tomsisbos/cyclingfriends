<?php

// Space for general error messages
displayMessage() ?>

<div class="container bg-admin">
    <div class="d-flex">
        <div class="rd-ad-name">
            <h2><?= $ride->name ?></h2>
            <p>管理ページ</p>
        </div>
        <a class="push" href="/ride/<?= $ride->id ?>">
            <button class="btn button box-shadow" type="button" name="edit">ライドページへ戻る</button>
        </a>
    </div>
</div>