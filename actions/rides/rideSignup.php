<?php

if (isset($_POST) && !empty($_POST)) {

    // Checking for missing fields
    $missing = [];
    if (!isset($_POST['email']) || empty($_POST['email'])) array_push($missing, 'メールアドレス');
    if (!isset($_POST['login']) || empty($_POST['login'])) array_push($missing, 'ユーザーネーム');
    if (!isset($_POST['password']) || empty($_POST['password'])) array_push($missing, 'パスワード');
    if (!isset($_POST['last_name']) || empty($_POST['last_name'])) array_push($missing, '姓');
    if (!isset($_POST['first_name']) || empty($_POST['first_name'])) array_push($missing, '名');
    if (!isset($_POST['birthdate']) || empty($_POST['birthdate'])) array_push($missing, '生年月日');

    // If missing fields have been detected, display an error message
    if (!empty($missing)) $errormessage = '記入漏れがあります。次の項目をご確認ください：' .implode('、', $missing);
    
    // Else, process account creation
    else {
        require '../actions/users/signup.php';
        if (isset($user->id)) { // If account has been correctly created

            // Set necessary profile data
            $user->update('last_name', htmlspecialchars($_POST['last_name']));
            $user->update('first_name', htmlspecialchars($_POST['first_name']));
            $user->update('birthdate', $_POST['birthdate']);
            $_POST = [];

            // Set success message
            session_start();
            $successmessage = '登録のメールアドレスに確認用のメールを送信しました。アカウント作成を完了するためには、そのメール内にある確認用URLをクリックしてください。';

            // Redirect to ride
            $uri_array = explode('/', $_SERVER['REQUEST_URI']);
            array_pop($uri_array);
            $uri = implode('/', $uri_array);
        }
    }

}