<?php

if (isset($entry_data) && !empty($entry_data)) {

    // Checking for missing fields
    $missing = [];
    if (!isset($entry_data['email']) || empty($entry_data['email'])) array_push($missing, 'メールアドレス');
    if (!isset($entry_data['login']) || empty($entry_data['login'])) array_push($missing, 'ユーザーネーム');
    if (!isset($entry_data['password']) || empty($entry_data['password'])) array_push($missing, 'パスワード');
    if (!isset($entry_data['last_name']) || empty($entry_data['last_name'])) array_push($missing, '姓');
    if (!isset($entry_data['first_name']) || empty($entry_data['first_name'])) array_push($missing, '名');
    if (!isset($entry_data['birthdate']) || empty($entry_data['birthdate'])) array_push($missing, '生年月日');

    // If missing fields have been detected, display an error message
    if (!empty($missing)) $errormessage = '記入漏れがあります。次の項目をご確認ください：' .implode('、', $missing);
    
    // Else, process account creation
    else {

        $redirect = true;
        require '../actions/users/signup.php';

        if (isset($user->id)) { // If account has been correctly created

            // Set necessary profile data
            $user->update('last_name', htmlspecialchars($entry_data['last_name']));
            $user->update('first_name', htmlspecialchars($entry_data['first_name']));
            $user->update('birthdate', $entry_data['birthdate']);

            // Set success message
            $successmessage = '登録のメールアドレスに確認用のメールを送信しました。アカウント作成を完了するためには、そのメール内にある確認用URLをクリックしてください。';

            // Prevent from multiple account creating intents
            if (isset($_POST['validate'])) unset($_POST['validate']);
        }
    }

}