<?php

include '../actions/users/unsubscribemail.php';
include '../includes/head.php'; ?>

<html>
    
    <link rel="stylesheet" href="/assets/css/home.css" />

    <style>
        .with-background-img::before {
            background: var(--bgImage);
        }
    </style>

    <body> <?php

        include '../includes/navbar.php'; ?>
                
        <div class="main with-background-img" data-parameter="public-scenery-imgs"> <?php
                            
            if (isset($errormessage)) echo '<div class="error-block absolute"><p class="error-message">' .$errormessage. '</p></div>';
            if (isset($successmessage)) echo '<div class="success-block absolute"><p class="success-message">' .$successmessage. '</p></div>'; ?>

            <div class="container-fluid end connection-page with-background-flash">
                <div class="home-main-text">メーリングリストから取り消したいメールアドレスを下記にご記入の上、送信ボタンをクリックしてください。</div>

                <form class="container smaller connection-container" method="post" id="unsubscribeMail">

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
                        <label class="form-label" for="floatingInput">Email address</label>
                    </div>

                    <button type="submit" class="btn button fullwidth button-primary" name="validate">送信</button>

                </form>
                
                <div class="js-scenery-info home-main-text mt-3"></div>

            </div>

        </div>

    </body>
</html>

<script src="/assets/js/animated-img-background.js"></script>