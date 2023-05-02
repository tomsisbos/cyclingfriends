<?php

include '../includes/head.php'; 
include '../actions/company/contactAction.php'; ?>

<!DOCTYPE html>
<html lang="en">

<link rel="stylesheet" href="/assets/css/home.css" />

<body class="black-theme"><?php

    include '../includes/navbar.php';
    
    // Space for general error messages
    include '../includes/result-message.php'; ?>

    <!-- Main container -->
    <div class="container home-container end">
        <form method="POST" class="container smaller">

            <h1 class="text-center mb-3">お問い合わせ</h1>
            
            <div class="mb-3 row g-2">
                <div class="col-md">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="lastname" name="lastname" <?php if (isset($_POST['lastname'])) echo 'value="' .$_POST['lastname']. '"'?>>
                        <label for="lastname" class="required">姓</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="firstname" name="firstname" <?php if (isset($_POST['firstname'])) echo 'value="' .$_POST['firstname']. '"'?>>
                        <label for="firstname" class="required">名</label>
                    </div>
                </div>
            </div>

            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" <?php if (isset($_POST['email'])) echo 'value="' .$_POST['email']. '"'?>>
                <label class="form-label" for="email">メールアドレス</label>
            </div>
                
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="title" name="title" <?php if (isset($_POST['title'])) echo 'value="' .$_POST['title']. '"'?>>
                <label class="form-label" for="title">タイトル</label>
            </div>
            
            <div class="mb-3">
                <textarea class="form-control" style="min-height: 200px" placeholder="本文" name="content"><?php  if (isset($_POST['content'])) echo $_POST['content']; ?></textarea>
            </div>
            
            <div class="btn-container">
                <button type="submit" class="btn button btnright btn-primary" name="send">送信</button>
            </div>

        </form>
    </div> <?php

    include '../includes/foot.php'; ?>

</body>
</html>

<script src="/assets/js/fade-on-scroll.js"></script>