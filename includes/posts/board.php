<link rel="stylesheet" href="/assets/css/posts.css">

<div class="container mb-0 pb-1">
    <h2>ニュース</h2>
</div>

<div class="container mb-3 bg-white posts-container posts-container-board"> <?php

    $offset = 0; $limit = 6;
    include '../actions/posts/getAllAction.php';
    if ($getPosts->rowCount() > 0) {
        while ($post_id = $getPosts->fetch(PDO::FETCH_COLUMN)) {
            $post = new Post();
            $post->load($post_id);
            include '../includes/posts/small-card.php';
        }
        if ($getResultsNumber->rowCount() > $limit) echo '<div class="text-right"><a href="/news">もっと表示する</a></div>';
    } else echo '表示するメッセージはありません。'; ?>

</div>