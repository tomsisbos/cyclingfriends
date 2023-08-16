<h2>新規アカウント作成のメールアドレス確認について</h2>

<p><a href="<?= $router->generate('user-signup') ?>">新規登録専用ページ</a>にて新規アカウントのログイン、メールアドレスとパスワードを登録後、<strong>アカウント作成の手続きを完了させるには、登録時にご入力いただいたメールアドレス宛に自動送信された確認用メールの中にあるURLをクリックする必要があります</strong>。件名は「アカウントのメールアドレス確認」になっているメールをお探しください。</p>
<p>確認用メールが見つからない場合は、<strong>スパムフォルダーに届いていたり、メールサーバーのセキュリティ設定で除外されていないかご確認ください。</strong></p>
<p>確認用メールの再送信を希望する方は、下記のフォームにアカウント情報をご記入の上、「再送信」ボタンをクリックしてください。</p>

<form class="container smaller connection-container" method="post">

    <div class="form-floating mb-3">
        <input type="email" class="form-control" id="floatingInput" placeholder="Email" name="email">
        <label class="form-label" for="floatingInput">メールアドレス</label>
    </div>
    <div class="form-floating mb-3">
        <input type="login" class="form-control" id="floatingInput" placeholder="Login" name="login">
        <label class="form-label" for="floatingInput">ユーザーネーム</label>
    </div>
    <div class="form-floating mb-3">
        <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password">
        <label class="form-label" for="floatingPassword">パスワード</label>
    </div>
    
    <button type="submit" class="btn button button-primary fullwidth" name="send">再送信</button>
    
</form>