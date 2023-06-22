<?php

use \SendGrid\Mail\Mail;

// Load all guides
$getRegisteredGuides = $db->prepare("SELECT user_id FROM user_guides");
$getRegisteredGuides->execute();
$guides = [];
while ($user_id = $getRegisteredGuides->fetch(PDO::FETCH_COLUMN)) {
    array_push($guides, new Guide($user_id));
}

// Add guide if necessary
if (isset($_POST['add'])) {
    if ($_POST['position'] == 'default') $errormessage = 'ポジションを選択してください。';
    else {
        $ride->addGuide($_POST['guide'], $_POST['position']);
        $added_guide = new Guide($_POST['guide'], $ride->id, $_POST['position']);
        $successmessage = '@' .$added_guide->login. 'が' .$added_guide->getPositionString(). 'として' .$ride->name. 'のガイドに追加されました！';

        // Send a mail to added guide address
        $email = new Mail();
        $email->setFrom(
            'contact@cyclingfriends.co',
            'CyclingFriends'
        );
        $email->setSubject('@' .$ride->getAuthor()->login. 'より「' .$ride->name. '」の「' .$added_guide->getPositionString(). '」ガイドに追加されました');
        $email->addTo($added_guide->email);
        $email->addContent(
            'text/html',
            '<p>いつもCyclingFriendsをご利用頂き、ありがとうございます。</p>
            <p>この度、<a href="' .$router->generate('profile-single', ['user_id' => $ride->getAuthor()->id]). '">@' .$ride->getAuthor()->login. '</a>が主催しているライド「' .$ride->name. '」に' .$added_guide->getPositionString(). 'ガイドとして追加してくれました。</p>
            <p>' .$ride->name. 'の<a href="' .$router->generate('ride-single', ['ride_id' => $ride->id]). '">専用ページ</a>にアクセスし、上部に表示されている「管理」ボタンにクリックすると、管理画面にアクセスして頂けます。</p>
            <p>本件に関して見覚えがない、あるいはガイド登録を却下したい場合は、<a href="' .$router->generate('ride-guide-requests'). '">こちらをクリックしてください。</p>'
        );
        $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
        $response = $sendgrid->send($email);
        var_dump($response);
    }
}

// Remove guide if necessary
if (isset($_POST['remove'])) {
    $ride->removeGuide($_POST['guide']);
    $removed_guide = new Guide($_POST['guide']);
    $successmessage = '@' .$removed_guide->login. 'が' .$ride->name. 'のガイドから取り消されました。';
}