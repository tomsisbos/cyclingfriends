<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if (isset($_GET)) {

    if (isset($_GET['email'])) echo json_encode($user->email);
    
    if (isset($_GET['login'])) echo json_encode($user->login);

    if (isset($_GET['privacy-settings'])) {
        $settings = $user->getSettings();
        unset($settings->id);
        echo json_encode($settings);
    }

    if (isset($_GET['connection-settings'])) {
        // Twitter
        $twitter = $user->getTwitter();
        if ($twitter->isUserConnected()) {
            $twitter_data = [
                'authenticateUrl' => '/api/settings.php',
                'connected' => $twitter->username
            ];
        } else {
            $twitter_data = [
                'authenticateUrl' => $twitter->getAuthenticateUrl($_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . '/settings'),
                'connected' => false
            ];
        }
        // Garmin
        $garmin = $user->getGarmin();
        if ($garmin->isUserConnected()) {
            $garmin_data = [
                'authenticateUrl' => '/api/settings.php',
                'connected' => true
            ];
        } else {
            $garmin_data = [
                'authenticateUrl' => $garmin->getAuthenticateUrl($user->id),
                'connected' => false
            ];
        }

        echo json_encode([
            'twitter' => $twitter_data,
            'garmin' => $garmin_data
        ]);
    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$settings = json_decode($json, true);

if (is_array($settings)) {

    if ($settings['type'] === 'settings') {
        unset($settings['type']);
        $response = $user->updateSettings($settings);
        if ($response === true) echo json_encode(['success' => '変更が保存されました。']);
        else echo json_encode(['error' => '保存中にエラーが発生しました。']);

    } else if ($settings['type'] === 'disconnections') {
        if ($settings['api'] === 'Twitter') {
            $twitter = $user->getTwitter();
            $twitter->disconnect();
            echo json_encode(['success' => 'Twitterとの接続が解除されました。']);
        } else if ($settings['api'] == 'Garmin Connect') {
            $garmin = $user->getGarmin();
            $garmin->deregister();
            echo json_encode(['success' => 'Garmin Connectとの接続が解除されました。']);
        }

    } else if ($settings['type'] === 'email') {
        $posted_email = htmlspecialchars($settings['email']);
        $posted_verification_email = htmlspecialchars($settings['emailVerification']);
        $posted_password = htmlspecialchars($settings['password']);
        // Check if posted email corresponds to posted verification email
        if ($posted_email == $posted_verification_email) {
            // Check if filled password matches connected user registered password
            if (password_verify($posted_password, $user->getPassword())) {    
                // Check if filled email format is valid
                if (filter_var($posted_email, FILTER_VALIDATE_EMAIL)) {
                    // Update user data
                    $updateEmail = $db->prepare('UPDATE users SET slug = FLOOR(RANDOM() * 1000000000), email = ?, verified = 0 WHERE id = ?');
                    $updateEmail->execute(array($posted_email, $user->id));
                    $user->sendVerificationMail(['redirect' => false]);
                    echo json_encode(['success' => '登録の新メールアドレス宛に確認用のメールを送信しました。新メールアドレスでご利用頂くために、そのメール内にある確認用URLをクリックしてください。']);
                } else echo json_encode(['error' => 'この形式のメールアドレスをご利用頂けません。メールアドレスの記載に誤字がないか、再度ご確認ください。']);
            } else echo json_encode(['error' => 'パスワードが一致していません。再度お試しください。']);
        } else echo json_encode(['error' => '確認用のメールアドレスがご記入頂いたメールアドレスと異なります。']);
        
    } else if ($settings['type'] === 'login') {
        $posted_login = htmlspecialchars($settings['login']);
        $posted_password = htmlspecialchars($settings['password']);
        // Check if filled password matches connected user registered password
        if (password_verify($posted_password, $user->getPassword())) {
            $updateLogin = $db->prepare('UPDATE users SET login = ? WHERE id = ?');
            $updateLogin->execute(array($posted_login, $user->id));
            echo json_encode(['success' => 'ユーザーネームが更新されました！']);
        } else echo json_encode(['error' => 'パスワードが一致していません。再度お試しください。']);
    
    } else if ($settings['type'] === 'password') {
        if (isset($settings['newPassword'])) {
            if (!empty($settings['currentPassword']) AND !empty($settings['newPassword'])){
                $current_password = htmlspecialchars($settings['currentPassword']);
                $new_password = htmlspecialchars($settings['newPassword']);
                
                // Check if filled password matches registered password
                if (password_verify($current_password, $user->getPassword())) {
                    // Check if the new passport is different as the former one
                    if ($current_password != $new_password) {
                        // Check if password format is valid
                        if ($user->checkPasswordStrength($new_password)) {
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
    } else if ($settings['type'] === 'deleteAccount') {
        $posted_password = htmlspecialchars($settings['password']);
        // Check if filled password matches connected user registered password
        if (password_verify($posted_password, $user->getPassword())) {
            $deleteAccount = $db->prepare('DELETE FROM users WHERE id = ?');
            $deleteAccount->execute(array($user->id));
            session_destroy();

            echo json_encode(['success' => 'アカウントとアカウントに付随されているデータは全て削除されました。']);
        } else echo json_encode(['error' => 'パスワードが一致していません。再度お試しください。']);
    }

}