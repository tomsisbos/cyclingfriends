<?php

include '../actions/users/initSessionAction.php';
include '../includes/head.php';
include '../actions/admin/postNewsAction.php';
include '../includes/navbar.php';?>

<!DOCTYPE html>
<html lang="en">

    <link rel="stylesheet" href="/assets/css/dev-news.css" />

    <div class="main">

        <h2 class="top-title">News</h2>
        <div class="container end bg-admin">

            <form class="adm-news-form" method="POST">
                
                <div class="adm-news-details">
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

                <div class="adm-news-content">
                    <div class="form-floating">
                        <textarea id="content" placeholder="内容" class="form-control fullheight" name="content"></textarea>
                        <label for="content">内容</label>
                    </div>
                </div>
                
			    <button type="submit" class="btn button adm-news-button" name="send">投稿</button>

            </form>

        </div>
    </div>

</html>