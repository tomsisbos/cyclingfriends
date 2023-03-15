<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    // Social or profile infos update
    if (isset($_GET['twitter']) OR isset($_GET['facebook']) OR isset($_GET['instagram']) OR isset($_GET['strava']) OR isset($_GET['last_name']) OR isset($_GET['first_name']) OR isset($_GET['gender']) OR isset($_GET['birthdate']) OR isset($_GET['level']) OR isset($_GET['description'])) {
        $index = key($_GET);
        $value = $_GET[$index];
        $updateInfo = $db->prepare("UPDATE users SET {$index} = ? WHERE id = ?");
        $updateInfo->execute(array($value, $connected_user->id));
        echo json_encode([$index, $value]);
    }

    // Bike infos update
    if (isset($_GET['bike-type']) OR isset($_GET['bike-model']) OR isset($_GET['bike-wheels']) OR isset($_GET['bike-components']) OR isset($_GET['bike-description'])) {
        $request = array_key_first($_GET);
        $index   = substr($request, 5);
        $value   = $_GET[$request];
        $id      = $_GET['id'];
        $bike = new Bike();

        // If value is 'new', create a new entry in bikes table and return the corresponding bike id
        if ($id == 'new') {
            $bike->create($connected_user->id);
            $id = $bike->id;
            
        // Else, update existing bike
        } else $bike->load($id);
        
        $bike->updateValue($index, $value);
        echo json_encode([$id, $index, $value]);
    }

    // Bike deleting
    if (isset($_GET['deleteBike'])) {
        $deleteBike = $db->prepare('DELETE FROM bikes WHERE id = ?');
        $deleteBike->execute(array($_GET['deleteBike']));
        echo json_encode([true, 'success' => 'バイクが削除されました。']);
    }
}