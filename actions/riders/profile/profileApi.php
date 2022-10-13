<?php
header('Content-Type: application/json, charset=UTF-8');
session_start();
require $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php';
Autoloader::register();
include $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';
include $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';

// In case an Ajax request have been detected
if (isAjax()) {
    
    if (isset($_GET['get_gallery_infos'])) {
        if ($_GET['id'] != 'null') {
            $user_id = $_GET['id'];
        } else {
            $user_id = $connected_user->id;
        }
        $getInfos = $db->prepare('SELECT id, user_id, img_id, size, name, type, caption FROM user_photos WHERE user_id = ? ORDER BY img_id');
        $getInfos->execute(array($user_id));
        $infos = $getInfos->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($infos);
    }

    if (isset($_POST['uploadProfileGallery'])) {
        $result = $connected_user->uploadProfileGallery();
        echo json_encode($result);
    }

    if (isset($_GET['deleteGallery'])) {
        $deleteGallery = $db->prepare('DELETE FROM user_photos WHERE user_id = ?');
        $deleteGallery->execute(array($connected_user->id));
        if ($deleteGallery->rowCount() > 0) {
            echo json_encode([true, 'Current gallery has been successfully deleted.']);
        } else {
            echo json_encode([false, 'You don\'t have set any gallery yet.']);
        }
    }

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
        // If value is 'new', create a new entry in bikes table and return the corresponding bike id
        if ($id == 'new') {
            $bikes           = $connected_user->getBikes();
            $new_bike_number = count($bikes) + 1;
            $type            = '';
            $model           = '';
            $components      = '';
            $wheels          = '';
            $description     = '';
            switch ($index) {
                case 'type': $type = $index; break;
                case 'model': $model = $index; break;
                case 'components': $components = $index; break;
                case 'wheels': $wheels = $index; break;
                case 'description': $description = $index; break;
            }
            $createBike = $db->prepare('INSERT INTO bikes(user_id, number, type, model, components, wheels, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $createBike->execute(array($connected_user->id, $new_bike_number, $type, $model, $components, $wheels, $description));
            $getBikeId = $db->prepare('SELECT id FROM bikes WHERE user_id = ? AND number = ?');
            $getBikeId->execute(array($connected_user->id, $new_bike_number));
            $id = $getBikeId->fetch()[0];
            echo json_encode([$id, $index, $value]);
        // Else, update existing bike
        } else {
            $updateInfo = $db->prepare("UPDATE bikes SET {$index} = ? WHERE id = ?");
            $updateInfo->execute(array($value, $id));
            echo json_encode([$id, $index, $value]);
        }
    }

    // Bike deleting
    if (isset($_GET['deleteBike'])) {
        $deleteBike = $db->prepare('DELETE FROM bikes WHERE id = ?');
        $deleteBike->execute(array($_GET['deleteBike']));
        if ($deleteBike->rowCount() > 0) {
            echo json_encode([true, 'Bike has been successfully deleted.']);
        } else {
            echo json_encode([false, 'This bike has already been deleted.']);
        }
    }
}


// In case a json file have been sent
$json = file_get_contents('php://input');
if (isset($json) AND !empty($json)) { // Get json file from gallery.js xhr request

    $var = json_decode($json, true);

    if (is_array($var)) {

        if (array_key_exists('updatecaption', $var)) { // Caption edition

            $updateCaption = $db->prepare('UPDATE user_photos SET caption = ? WHERE user_id = ? AND img_id = ? ');
            $updateCaption->execute(array(htmlspecialchars($var['caption']), $connected_user->id, $var['img_id']));
            
            echo json_encode($var);
        
        }
    }
}