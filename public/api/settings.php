<?php

require '../../includes/api-head.php';

if (isset($_GET)) {

    if (isset($_GET['email'])) echo json_encode($connected_user->email);

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
        if ($response === true) echo json_encode(['success' => 'Your changes have been updated.']);
        else echo json_encode(['error' => 'An error has occured during saving process.']);

    } else if ($settings['type'] === 'email') {
        $posted_email = htmlspecialchars($settings['email']);
        $posted_password = htmlspecialchars($settings['password']);
        //Check if filled password matches connected user registered password
        if (password_verify($posted_password, $connected_user->getPassword())) {    
            // Check if filled email format is valid
            if (filter_var($posted_email, FILTER_VALIDATE_EMAIL)) {  
                $updateEmail = $db->prepare('UPDATE users SET email = ? WHERE id = ?');
                $updateEmail->execute(array($posted_email, $connected_user->id));
                echo json_encode(['success' => 'Your email address has correctly been updated !']);
            } else echo json_encode(['error' => 'This is not a valid email address. Please try again.']);
        } else echo json_encode(['error' => 'Your password is not correct. Please try again.']);
    
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
                            echo json_encode(['success' => 'Your password has correctly been updated !']);
                        } else echo json_encode(['error' => 'Your password must be at least 6 characters long.']);
                    } else echo json_encode(['error' => 'Your new password is the same as the current one. It won\'t be updated.']);
                } else echo json_encode(['error' => 'The current password you entered is not correct. Please try again.']);
            } else echo json_encode(['error' => 'Your password field is empty. You need to fill in a password.']);
        }
    }
}