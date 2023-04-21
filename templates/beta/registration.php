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
                
        <div class="main with-background-img" data-parameter="public-scenery-imgs"> <?php
                            
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
                        <input type="email" class="form-control" id="floatingInput" placeholder="メールアドレス" name="email"<?php
                                if (isset($_POST['email'])) echo 'value="' .$_POST['email']. '"'; ?>
                            >
                        <label class="form-label required" for="floatingInput">メールアドレス</label>
                    </div>
                    <div class="d-flex gap mb-3">
                        <div class="form-floating col-3 flex-grow-1">
                            <input type="text" class="form-control" id="floatingInput1" placeholder="姓" name="lastname"<?php
                                if (isset($_POST['lastname'])) echo 'value="' .$_POST['lastname']. '"'; ?>
                            >
                            <label class="form-label required" for="floatingInput1">姓</label>
                        </div>
                        <div class="form-floating col-3 flex-grow-1">
                            <input type="text" class="form-control" id="floatingInput2" placeholder="名" name="firstname"<?php
                                if (isset($_POST['firstname'])) echo 'value="' .$_POST['firstname']. '"'; ?>
                            >
                            <label class="form-label required" for="floatingInput2">名</label>
                        </div>
                    </div>
                    <div class="d-flex gap mb-3">
                        <div class="form-floating col-3">
                            <input type="text" class="form-control" id="floatingInput" placeholder="郵便番号" name="zipcode"<?php
                                if (isset($_POST['zipcode'])) echo 'value="' .$_POST['zipcode']. '"'; ?>
                            >
                            <label class="form-label required" for="floatingInput">郵便番号</label>
                        </div>
                        <div class="form-floating col-3 flex-grow-1">
                            <input type="text" class="form-control" id="floatingInput" placeholder="住所" name="address"<?php
                                if (!empty($_POST) && !empty($post_code['results'][0]) && substr($_POST['address'], 0, 2) != substr($post_code['results'][0]['address1'], 0, 2)) echo 'value="' .$post_code['results'][0]['address1'] . $post_code['results'][0]['address2'] . $post_code['results'][0]['address3']. '"';
                                else if (!empty($_POST) && !empty($_POST['address'])) echo 'value="' .$_POST['address']. '"'; ?>
                            >
                            <label class="form-label required" for="floatingInput">住所</label>
                        </div>
                    </div>
                    <div class="terms-agreement mb-1">
                        <p>
                            ・プライベートベータ期間中に、CyclingFriendsサービス内部の機能、データやその他非公開になっているものに関する外部発信は認めます。但し、現時点でのサービスは<strong>未完成な状態であることをご理解頂き</strong>、利用中に発生した<strong>バグ等の不具合に関しては発信を控えて頂きますようお願い致します。</strong><br>
                            ・ベータバージョンの一般公開に向け、順次不具合の解消に務めて参ります。不具合を確認した場合は、専用ツールを使って、<strong>極力ご報告頂きますようお願い致します</strong>。<br>
                            ・プライベートベータであっても、実施期間中にサービス利用中に保存されるデータ（アクティビティ、絶景スポット、ユーザーアカウント等）はサーバー上で保存され、<strong>実施期間が終了したあともそのまま繰り越される</strong>ので、<a target="_blank" href="/manual">データの取り扱いに関するガイドライン</a>に即して、他機能や他サービスに活用されることをご了承ください。
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