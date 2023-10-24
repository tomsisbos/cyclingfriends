<?php

include '../actions/users/initPublicSession.php';
include '../includes/head.php';
include '../actions/posts/post.php';
include '../includes/navbar.php';?>

<!DOCTYPE html>
<html lang="en">

<body <?php if (!isSessionActive()) echo ' class="black-theme"' ?>>

    <link rel="stylesheet" href="/assets/css/posts.css" />

    <div class="main"> <?php

		// Space for general error messages
		include '../includes/result-message.php'; ?>

        <h2 class="top-title">ニュース</h2>

        <div class="container posts-container"> <?php
    
            // Define offset and number of posts to query
            $limit = 20;
            if (isset($_GET['p'])) $offset = ($_GET['p'] - 1) * $limit;
            else $offset = 0;

            include '../actions/posts/getAll.php';
            while ($post_id = $getPosts->fetch(PDO::FETCH_COLUMN)) {
                $post = new Post();
                $post->load($post_id);
                include '../includes/posts/small-card.php';
            }
            
            // Set pagination system
            if ($getResultsNumber->rowCount() > $limit) {
                if (isset($_GET['p'])) $p = $_GET['p'];
                else $p = 1;
                $url = strtok($_SERVER["REQUEST_URI"], '?');
                $total_pages = ceil($getResultsNumber->rowCount() / $limit);
                include '../includes/pagination.php';
            } ?>
        </div> <?php
        
        if (isSessionActive() AND getConnectedUser()->hasModeratorRights()) { ?>

            <div class="container bg-admin mb-3">

                <h3>新規投稿</h3>

                <form class="d-flex flex-column gap" method="POST">
                    
                    <div class="adm-posts-form">
                        <div class="adm-posts-details">
                            <div class="col-md">
                                <div class="form-floating">
                                    <input type="text" id="title" class="form-control" placeholder="タイトル" name="title">
                                    <label for="title">タイトル</label>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="form-floating">
                                    <select class="form-control" id="type" name="type">
                                        <option value="dev">開発</option>
                                        <option value="general">一般</option>
                                    </select>
                                    <label for="type">タイプ</label>
                                </div>
                            </div>
                        </div>

                        <div class="adm-posts-content">
                            <div class="form-floating">
                                <textarea id="content" placeholder="内容" class="form-control fullheight" name="content"></textarea>
                                <label for="content">内容</label>
                            </div>
                        </div>
                    </div>

                    <div>
                        <input type="checkbox" name="twitter" id="twitter">
                        <label for="twitter">Twitterで自動投稿する</label>
                    </div>
                    
                    <button type="submit" class="btn button adm-posts-button" name="send">投稿</button>

                </form>

            </div> <?php
        } ?>
    </div>

</body>

</html>