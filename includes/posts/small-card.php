<div class="post-block">
    <div class="post-top">
        <div class="post-datetime"><?= $post->datetime->format('Y-m-d') ?></div>
        <div class="post-type"><?= $post->getTypeString() ?></div>
        <div class="post-title"><?= $post->title ?></div>
    </div>
    <div class="post-content"><?= $post->content ?></div>
</div>