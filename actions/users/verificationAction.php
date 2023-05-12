<?php

require '../actions/databaseAction.php';

// If slug and email correspond
$checkIfSlugAndEmailCorresponds = $db->prepare("SELECT id FROM users WHERE slug = ? AND email = ?");
$checkIfSlugAndEmailCorresponds->execute([$user_slug, $email]);
if ($checkIfSlugAndEmailCorresponds->rowCount() == 1) {

    $user_id = intval($checkIfSlugAndEmailCorresponds->fetch(PDO::FETCH_COLUMN));

    // Set verified to true
    $setUserToVerified = $db->prepare("UPDATE users SET verified = 1 WHERE id = ?");
    $setUserToVerified->execute([$user_id]);
    
    $user = new User($user_id);

    session_start();

    $user->setSession();

    $_SESSION['successmessage'] = 'アカウント作成が無事に完了しました！';

    // Redirect authentified user to the Dashboard
    if (isset($url)) header('location: ' .$url);
    else header('location: /dashboard');
    exit();

} else header('location: /');