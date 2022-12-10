<?php

require '../../includes/api-head.php';

if (isset($_GET)) {

    if (isset($_GET['privacy-settings'])) {
        $settings = $connected_user->getSettings();
        unset($settings->id);
        echo json_encode($settings);
    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$settings = json_decode($json, true);

if (is_array($settings)) {
    $response = $connected_user->updateSettings($settings);
    if ($response = true) json_encode(true);
    else json_encode(false);
}