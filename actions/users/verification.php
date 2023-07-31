<?php

require '../actions/database.php';

// If slug and email correspond
$checkIfSlugAndEmailCorresponds = $db->prepare("SELECT id FROM users WHERE slug = ? AND email = ?");
$checkIfSlugAndEmailCorresponds->execute([$user_slug, $email]);
if ($checkIfSlugAndEmailCorresponds->rowCount() == 1) {

    $user_id = intval($checkIfSlugAndEmailCorresponds->fetch(PDO::FETCH_COLUMN));

    // Set verified to true    
    $user = new User($user_id);
    $user->verify();

    session_start();

    $user->setSession();

    $_SESSION['successmessage'] = 'メールアドレスが無事に確認できました！';

    // Redirect authentified user to the Dashboard
    if (isset($url)) header('location: ' .$url);
    else header('location: /dashboard');
    exit();

} else header('location: /');