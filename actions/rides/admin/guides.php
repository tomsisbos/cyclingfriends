<?php

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
    }
}

// Add guide if necessary
if (isset($_POST['remove'])) {
    var_dump($_POST);
    $ride->removeGuide($_POST['guide']);
    $removed_guide = new Guide($_POST['guide']);
    $successmessage = '@' .$added_guide->login. 'が' .$ride->name. 'のガイドから取り消されました。';
}