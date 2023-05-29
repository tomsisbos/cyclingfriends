<?php

require '../actions/databaseAction.php';

// Get user infos
$token = basename($_SERVER['REQUEST_URI']);
$getUserIdFromToken = $db->prepare("SELECT user_id FROM user_resetpassword_token WHERE token = ? AND expiration_date < (NOW() + INTERVAL 1 DAY)");
$getUserIdFromToken->execute([$token]);

// If token exists and is not expired
if ($getUserIdFromToken->rowCount() > 0) {
    $user = new User($getUserIdFromToken->fetch(PDO::FETCH_COLUMN));

    if (isset($_POST['validate'])) {

        // Check if user completed all fields
        if (!empty($_POST['password'])) {

            $new_password = htmlspecialchars($_POST['password']);
            
            // Check if password format is valid
            if ($user->checkPasswordStrength($new_password)) {

                // Hash new password
                $encrypted_password = password_hash($new_password, PASSWORD_DEFAULT);
                $updatePassword = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                $updatePassword->execute(array($encrypted_password, $user->id));

                // Destroy token
                $destroyToken = $db->prepare("DELETE FROM user_resetpassword_token WHERE token = ?");
                $destroyToken->execute([$token]);
                
                $successmessage = 'パスワードが更新されました！新しいパスワードを用いて、<a href="' .$router->generate('user-signin'). '">こちら</a>からログインしてください。';

            } else $errormessage = '6文字以上のパスワードを入力してください。';

        } else $errormessage = "パスワードが空欄になっています。6文字以上のパスワードを入力してください。";
    }

// Else, display an error message and prevent form display
} else {
    $invalid_token = true;
    $errormessage = '有効なトークンではありません。該当するユーザーがパスワードの再発行を希望していないか、期限が過ぎた可能性があります。<a href="' .$router->generate('user-reset-password-application'). '">こちら</a>にアクセスし、パスワード再発行手続きをやり直してください。';
}