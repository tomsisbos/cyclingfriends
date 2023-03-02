<?php

require '../actions/databaseAction.php';

// Get token
$last_parameter = basename($_SERVER['REQUEST_URI']);
if (is_numeric($last_parameter)) $token = $last_parameter;
else header('location: /');

// Check if token corresponds to a database entry
$checkIfTokenIsValid = $db->prepare("SELECT id FROM mailing_list WHERE token = ?");
$checkIfTokenIsValid->execute([$token]);
if ($checkIfTokenIsValid->rowCount() > 0) {

    // Check if an user has already been created with this token
    $checkIfCorrespondingUserExists = $db->prepare("SELECT user_id FROM privatebeta_members WHERE token = ? AND user_id IS NOT NULL");
    $checkIfCorrespondingUserExists->execute([$token]);
    if ($checkIfCorrespondingUserExists->rowCount() == 0) {
        
        // If data has been posted
        if (isset($_POST)) {
            
            // If data is not empty
            if (!empty($_POST['email']) && !empty($_POST['firstname']) && !empty($_POST['lastname']) && !empty($_POST['address']) && !empty($_POST['zipcode'])) {

                $email = htmlspecialchars($_POST['email']);
                $firstname = htmlspecialchars($_POST['firstname']);
                $lastname = htmlspecialchars($_POST['lastname']);
                $zipcode = htmlspecialchars($_POST['zipcode']);
                $address = htmlspecialchars($_POST['address']);
                if ($_POST['agreement'] == 'on') $agreement = true;
                else $agreement = false;

                // Check agreement
                if (!$agreement) $errormessage = 'ご登録頂くには、利用規約に同意して頂く必要があります。';

                // Check zipcode validity
                $posturl = "http://zipcloud.ibsnet.co.jp/api/search?zipcode={$zipcode}";
                $post_code = json_decode(file_get_contents($posturl), true);
                if ($post_code["results"] == null) $errormessage = "郵便番号は実在しません。";

                // Check prefecture and zipcode matching
                else if (substr($address, 0, strlen($post_code['results'][0]['address1'])) != $post_code['results'][0]['address1']) $errormessage = "ご記入頂いた郵便番号と住所が一致しません。正しい郵便番号と住所をご記入ください。";

                // Check zipcode format
                $zipcode = mb_convert_kana($zipcode, "n"); // Convert to alphanumeric
                $zipcode = preg_replace("/[^0-9]/", "", $zipcode); // Remove all characters that are not numbers
                if (mb_strlen($zipcode) != 7) $errormessage = "郵便番号の桁数が正しくありません。";

                // Check email validity
                $checkIfEmailIsRegistered = $db->prepare("SELECT email FROM mailing_list WHERE email = ?");
                $checkIfEmailIsRegistered->execute([$email]);
                if ($checkIfEmailIsRegistered->rowCount() == 0) $errormessage = "お手数ですが、メーリングリストにご登録頂いていないメールアドレスは、プライベートベータの登録にご利用頂けません。";
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errormessage = "正しいフォーマットのメールアドレスをご記入ください。";

                // If everything is correct, register member.
                if (!isset($errormessage)) {

                    $registerPrivateBetaInfos = $db->prepare("INSERT INTO privatebeta_members(token, email, first_name, last_name, zipcode, address, agreement) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $registerPrivateBetaInfos->execute(array($token, $email, $firstname, $lastname, $zipcode, $address, $agreement));

                    $successmessage = 'ご登録頂き、ありがとうございます！近日中にアカウント作成のご案内をお送りさせて頂きます。';

                    $_POST = [];
                }

            } else $errormessage = '全ての情報をご記入ください。';
            
        }

    } else {

        $user_id = $checkIfCorrespondingUserExists->fetch(PDO::FETCH_COLUMN);
        if ($user_id) {
            
            $user = new User($user_id) ?>
        
            <div class="text-center m-5">
                「<?= $user->login ?>」というユーザー名でアカウントを既に作成しています。そちらをお使いください。
            </div> <?php
            die();
        }

    }

} else header('location: /');