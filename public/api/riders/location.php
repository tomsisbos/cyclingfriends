<?php

header('Content-Type: application/json, charset=UTF-8');

require '../../../includes/api-head.php';

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);

if (is_array($data)) {

    $success = $connected_user->setLocation(new Geolocation($data['geolocation']['city'], $data['geolocation']['prefecture']), new lngLat($data['lngLat']['lng'], $data['lngLat']['lat']));
    if ($success) $response = ['success' => 'Your location has been updated !'];
    else $response = ['error' => 'An error has occured during location updating process.'];
    echo json_encode($response);

}

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get-location'])) {

        if ($_GET['get-location'] == false) $user = $connected_user;
        else $user = new User($_GET['get-location']);
        echo json_encode($user->lngLat);

    }

}