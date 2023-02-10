<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['is-bike-accepted'])) {
        $ride = new Ride($_GET['is-bike-accepted']);
        if ($ride->isBikeAccepted($connected_user)) echo json_encode(['answer' => true, 'bikes_list' => $ride->getAcceptedBikesString()]);
        else echo json_encode(['answer' => false, 'bikes_list' => $ride->getAcceptedBikesString()]);
    }

    if (isset($_GET['get-questions'])) {
        $ride = new Ride($_GET['get-questions']);
        $questions = $ride->getAdditionalFields();
        echo json_encode($questions);
    }

    if (isset($_GET['get-terrain-value'])) {
        $route = new Route($_GET['get-terrain-value']);
        echo json_encode($route->getTerrainValue());
    }

    if (isset($_GET['ride-delete'])) {
        $ride = new Ride($_GET['ride-delete']);
        if ($connected_user->id == $ride->author_id) $ride->delete();
        else "このライドを削除する権限はありません。";
        echo json_encode($connected_user->login);
    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$var = json_decode($json, true);

if (is_array($var)) {

    if ($var['type'] == 'post-answers') {
        $data = $var['data'];
        foreach ($data as $answer) {
            $field_id = $answer['id'];
            $answer = $answer['answer'];
            $field = new AdditionalField($field_id);
            $field->setAnswer($connected_user->id, $answer);
        }
        echo json_encode(true);
    }

}