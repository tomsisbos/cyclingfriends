<div class="container mt-3">
    <h3>アカウント作成に必要な情報</h3>

    <!-- Email -->
    <div class="form-floating mb-3">
        <input type="email" name="email" class="form-control js-field" id="floatingEmail" placeholder="Email"<?php if (isset($_POST['email'])) echo ' value="' .$_POST['email']. '"'?>>
        <label class="form-label" for="floatingEmail">メールアドレス</label>
    </div>

    <!-- Login -->
    <div class="form-floating mb-3">
        <input type="login" name="login" class="form-control js-field" id="floatingLogin" placeholder="Login"<?php if (isset($_POST['login'])) echo ' value="' .$_POST['login']. '"'?>>
        <label class="form-label" for="floatingLogin">ユーザーネーム</label>
    </div>

    <!-- Password -->
    <div class="form-floating mb-3">
        <input type="password" name="password" class="form-control js-field" id="floatingPassword" placeholder="Password"<?php if (isset($_POST['password'])) echo ' value="' .$_POST['password']. '"'?>>
        <label class="form-label" for="floatingPassword">パスワード</label>
    </div>
</div>
        
<div class="container mt-3">
    <h3>エントリーに必要な情報</h3>

    <!-- Name -->
    <div class="d-flex justify-content-between">
        <div class="form-floating col-5 mb-3">
            <input name="last_name" type="text" id="floatingLastName" placeholder="姓" class="form-control js-field"<?php if (isset($entry_data['last_name'])) echo ' value="' .$entry_data['last_name']. '"'?>>
            <label class="form-label" for="floatingLastName">姓</label>
        </div>
        <div class="form-floating col-5 mb-3">
            <input name="first_name" type="text" id="floatingFirstName" placeholder="名" class="form-control js-field"<?php if (isset($entry_data['first_name'])) echo ' value="' .$entry_data['first_name']. '"'?>>
            <label class="form-label" for="floatingFirstName">名</label>
        </div>
    </div> 

    <!-- Birthdate -->
    <div class="form-floating mb-3">
        <input name="birthdate" type="date" class="form-control js-field" id="floatingBirthdate" min="1900-1-1" max="<?php date('Y-m-d'); ?>"<?php if (isset($entry_data['birthdate'])) echo ' value="' .$entry_data['birthdate']. '"'?>>
        <label class="form-label" for="floatingBirthdate">生年月日</label>
    </div>
</div>
