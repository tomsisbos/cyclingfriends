<?php

require '../../includes/api-head.php';

if (isset($_GET)) {

    if (isset($_GET['email'])) echo json_encode($connected_user->email);
    
    if (isset($_GET['login'])) echo json_encode($connected_user->login);

    if (isset($_GET['privacy-settings'])) {
        $settings = $connected_user->getSettings();
        unset($settings->id);
        echo json_encode($settings);
    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$settings = json_decode($json, true);

if (is_array($settings)) {

    if ($settings['type'] === 'settings') {
        unset($settings['type']);
        $response = $connected_user->updateSettings($settings);
        if ($response === true) echo json_encode(['success' => '変更が保存されました。']);
        else echo json_encode(['error' => '保存中にエラーが発生しました。']);

    } else if ($settings['type'] === 'email') {
        $posted_email = htmlspecialchars($settings['email']);
        $posted_password = htmlspecialchars($settings['password']);
        //Check if filled password matches connected user registered password
        if (password_verify($posted_password, $connected_user->getPassword())) {    
            // Check if filled email format is valid
            if (filter_var($posted_email, FILTER_VALIDATE_EMAIL)) {  
                $updateEmail = $db->prepare('UPDATE users SET email = ? WHERE id = ?');
                $updateEmail->execute(array($posted_email, $connected_user->id));
                echo json_encode(['success' => 'メールアドレスが更新されました！']);
            } else echo json_encode(['error' => 'この形式のメールアドレスをご利用頂けません。メールアドレスの記載に誤字がないか、再度ご確認ください。']);
        } else echo json_encode(['error' => 'パスワードが一致していません。再度お試しください。']);
        
    } else if ($settings['type'] === 'login') {
        $posted_login = htmlspecialchars($settings['login']);
        $posted_password = htmlspecialchars($settings['password']);
        // Check if filled password matches connected user registered password
        if (password_verify($posted_password, $connected_user->getPassword())) {
            $updateLogin = $db->prepare('UPDATE users SET login = ? WHERE id = ?');
            $updateLogin->execute(array($posted_login, $connected_user->id));
            echo json_encode(['success' => 'ユーザーネームが更新されました！']);
        } else echo json_encode(['error' => 'パスワードが一致していません。再度お試しください。']);
    
    } else if ($settings['type'] === 'password') {
        if (isset($settings['newPassword'])) {
            if (!empty($settings['currentPassword']) AND !empty($settings['newPassword'])){
                $current_password = htmlspecialchars($settings['currentPassword']);
                $new_password = htmlspecialchars($settings['newPassword']);
                
                // Check if filled password matches registered password
                if (password_verify($current_password, $connected_user->getPassword())) {
                    // Check if the new passport is different as the former one
                    if ($current_password != $new_password) {
                        // Check if password format is valid
                        if ($connected_user->checkPasswordStrength($new_password)) {
                            // Hash new password
                            $encrypted_password = password_hash($new_password, PASSWORD_DEFAULT);
                            $updatePassword = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
                            $updatePassword->execute(array($encrypted_password, $_SESSION['id']));
                            echo json_encode(['success' => 'パスワードが更新されました！']);
                        } else echo json_encode(['error' => '6文字以上のパスワードを入力してください。']);
                    } else echo json_encode(['error' => '現在利用中のパスワードと違うパスワードを入力してください。']);
                } else echo json_encode(['error' => 'パスワードが一致していません。再度お試しください。']);
            } else echo json_encode(['error' => 'パスワードが空欄になっています。6文字以上のパスワードを入力してください。']);
        }
    }
}