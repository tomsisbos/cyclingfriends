<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    $success = $connected_user->setLocation(new Geolocation($data['geolocation']['city'], $data['geolocation']['prefecture']), new lngLat($data['lngLat'][0], $data['lngLat'][1]));
    if ($success) $response = ['success' => '位置情報が更新されました！'];
    else $response = ['error' => '位置情報の変更が出来ませんでした。'];
    echo json_encode($response);

}

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-location'])) {
        if (is_numeric($_GET['get-location'])) $user = new User($_GET['get-location']);
        else if (isset($connected_user)) $user = $connected_user;
        else $user = null;
        if ($user) echo json_encode($user->lngLat);
        else echo json_encode(null);

    }

}