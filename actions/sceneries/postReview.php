<?php

if (isset($_POST) AND !empty($_POST)) {
    $successmessage = "レビューが投稿されました！";
    $scenery->postReview($_POST['sceneryReview']);
}