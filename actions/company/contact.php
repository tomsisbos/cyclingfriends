<?php

use \SendGrid\Mail\Mail;

if (isset($_POST) && !empty($_POST)) {

    if (!empty($_POST['lastname']) && !empty($_POST['firstname']) && !empty($_POST['title']) && !empty($_POST['email']) && !empty($_POST['content'])) {
    
        // Check if email address is valid
        if (filter_var(htmlspecialchars($_POST['email']), FILTER_VALIDATE_EMAIL)) {

            // Treat data
            $lastname = htmlspecialchars($_POST['lastname']);
            $firstname = htmlspecialchars($_POST['firstname']);
            $title = htmlspecialchars($_POST['title']);
            $address = htmlspecialchars($_POST['email']);
            $content = nl2br(htmlspecialchars($_POST['content']));

            // Send mail to contact@cyclingfriends.co
            $email = new Mail();
            $email->setFrom(
                'contact@cyclingfriends.co',
                $lastname. ' ' .$firstname
            );
            $email->setSubject('お問い合わせ：「' .$title. '」');
            $email->addTo('contact@cyclingfriends.co');
            $email->addContent(
                'text/html',
                "<p><strong>姓：</strong>" .$lastname. "</p>
                <p><strong>名：</strong>" .$firstname. "</p>
                <p><strong>メール：</strong>" .$address. "</p>
                <p><strong>件名：</strong>" .$title. "</p>
                <br>" .$content
            );
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);

            // Send verification mail to sender
            $email = new Mail();
            $email->setFrom(
                'contact@cyclingfriends.co',
                'CyclingFriends'
            );
            $email->setSubject('お問い合わせありがとうございます。');
            $email->addTo($address);
            $email->addContent(
                'text/html',
                "
                <p>この度、お問い合わせ頂きありがとうございます。問い合わせの内容を下記の通り受付させて頂きました。</p>
                <p>
                    <strong>姓：</strong>" .$lastname. "<br>
                    <strong>名：</strong>" .$firstname. "<br>
                    <strong>メール：</strong>" .$address. "<br>
                    <strong>件名：</strong>" .$title. "
                </p>
                <br>" .$content. "
                <p>---</p>
                <p>近日中にご回答させて頂きます。</p>
                <p>株式会社テラインコグニタ　スタッフ一同</p>"
            );
            $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
            $response = $sendgrid->send($email);


            $successmessage = "お問い合わせの内容が送信されました。近日中にご対応させて頂きます。";

        } else $errormessage = "正しい形式のメールアドレスをご記入ください。";

    } else $errormessage = "全ての情報をご記入ください。";

}