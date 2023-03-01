<?php

include '../includes/head.php';
require '../actions/beta/registerAction.php'; ?>

<html>
    
    <link rel="stylesheet" href="/assets/css/home.css" />

    <style>
        .with-background-img::before {
            background: var(--bgImage);
        }
    </style>

    <body> <?php

        include '../includes/navbar.php'; ?>
                
        <div class="main with-background-img"> <?php
                            
            if (isset($errormessage)) echo '<div class="error-block absolute"><p class="error-message">' .$errormessage. '</p></div>';
            if (isset($successmessage)) echo '<div class="success-block absolute"><p class="success-message">' .$successmessage. '</p></div>'; ?>

            <div class="container-fluid end connection-page with-background-flash"> 

                <form class="container smaller opaque-container" method="post">

                    <div class="classy-title">プライベートベータプログラムへのご登録</div>
                    <p class="text-center mb-3">
                        プライベートベータプログラムへご参加頂ける方は、下記の情報をご記入の上、ご登録ください。<br>
                        ご登録いただいた方には、プログラム開始時にアカウント作成のご案内をお送り致します。
                    </p>

                    <div class="form-floating mb-3">
                        <input type="email" class="form-control" id="floatingInput" placeholder="メールアドレス" name="email">
                        <label class="form-label required" for="floatingInput">メールアドレス</label>
                    </div>
                    <div class="d-flex gap mb-3">
                        <div class="form-floating col-3 flex-grow-1">
                            <input type="text" class="form-control" id="floatingInput1" placeholder="姓" name="lastname">
                            <label class="form-label required" for="floatingInput1">姓</label>
                        </div>
                        <div class="form-floating col-3 flex-grow-1">
                            <input type="text" class="form-control" id="floatingInput2" placeholder="名" name="firstname">
                            <label class="form-label required" for="floatingInput2">名</label>
                        </div>
                    </div>
                    <div class="d-flex gap mb-3">
                        <div class="form-floating col-3">
                            <input type="text" class="form-control" id="floatingInput" placeholder="郵便番号" name="zipcode">
                            <label class="form-label required" for="floatingInput">郵便番号</label>
                        </div>
                        <div class="form-floating col-3 flex-grow-1">
                            <input type="text" class="form-control" id="floatingInput" placeholder="住所" name="address">
                            <label class="form-label required" for="floatingInput">住所</label>
                        </div>
                    </div>
                    <div class="terms-agreement mb-1">
                        <p>
                            こちらが利用契約になります。
                            利用契約には、同意して頂く必要がありますので、ご理解のほど、どうぞ宜しくお願い致します。
                            こちらが利用契約になります。
                            利用契約には、同意して頂く必要がありますので、ご理解のほど、どうぞ宜しくお願い致します。
                            こちらが利用契約になります。
                            利用契約には、同意して頂く必要がありますので、ご理解のほど、どうぞ宜しくお願い致します。
                            こちらが利用契約になります。
                            利用契約には、同意して頂く必要がありますので、ご理解のほど、どうぞ宜しくお願い致します。
                        </p>
                    </div>
                    <div class="form-floating justify-center d-flex gap mb-3">
                        <input type="checkbox" name="agreement">
                        <p class="required">利用規約をすべて読み、同意します</p>
                    </div>

                    <button type="submit" class="btn button fullwidth button-primary" name="validate">登録</button>

                </form>

            </div>

        </div>

    </body>
</html>

<script src="/assets/js/animated-img-background.js"></script>