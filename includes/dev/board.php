<link rel="stylesheet" href="/assets/css/dev-news.css">

<div class="container bg-admin mb-3">
    <h2>開発の進捗状況</h2> <?php

    $getDevNews = $db->prepare("SELECT id FROM dev_news ORDER BY datetime DESC");
    $getDevNews->execute();

    if ($getDevNews->rowCount() > 0) {
        while ($id = $getDevNews->fetch(PDO::FETCH_COLUMN)) {
            $dev_new = new DevNew();
            $dev_new->load($id); ?>

            <div class="dev-new-block">
                <div class="dev-new-top">
                    <div class="dev-new-datetime"><?= $dev_new->datetime->format('Y-m-d') ?></div>
                    <div class="dev-new-type"><?= $dev_new->getTypeString() ?></div>
                    <div class="dev-new-title"><?= $dev_new->title ?></div>
                </div>
                <div class="dev-new-content"><?= $dev_new->content ?></div>
            </div> <?php
        }
    } else echo '表示するメッセージはありません。'; ?>

</div>