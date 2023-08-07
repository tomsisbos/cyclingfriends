<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['is-bike-accepted'])) {
        $ride = new Ride($_GET['is-bike-accepted']);
        if ($ride->isBikeAccepted(getConnectedUser())) echo json_encode(['answer' => true, 'bikes_list' => $ride->getAcceptedBikesString()]);
        else echo json_encode(['answer' => false, 'bikes_list' => $ride->getAcceptedBikesString()]);
    }

    if (isset($_GET['get-missing-information'])) {
        $missing_information = [];
        if (empty(getConnectedUser()->first_name) || empty(getConnectedUser()->last_name)) array_push($missing_information, '実名');
        if (!getConnectedUser()->birthdate) array_push($missing_information, '生年月日');
        echo json_encode($missing_information);
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
        if (getConnectedUser()->id == $ride->author_id) $ride->delete();
        else "このツアーを削除する権限はありません。";
        echo json_encode(getConnectedUser()->login);
    }

    if (isset($_GET['quit'])) {
        $ride = new Ride($_GET['quit']);
        if ($ride->isParticipating(getConnectedUser())) {
            $ride->quit(getConnectedUser());
            echo json_encode(['success' => $ride->name. 'への参加を取り消しました。エントリー期間中であれば、いつでもエントリーできます。']);		
        } else echo json_encode(['false' => $errormessage = $ride->name. 'への参加を既に取り消しています。']);
    }

}

// In case a Json request have been detected
$json = file_get_contents('php://input'); // Get json file from xhr request
$var = json_decode($json, true);

if (is_array($var)) {

    if ($var['type'] == 'post-answers') {
        $data = $var['data'];
        $ride_id = $var['id'];
        foreach ($data as $answer) {
            $field_id = $answer['id'];
            $answer = $answer['answer'];
            $field = new AdditionalField($field_id);
            $field->setAnswer(getConnectedUser()->id, $answer);
        }

        // If connected user has not already joined,
        $ride = new Ride($ride_id);
        if (!$ride->isParticipating(getConnectedUser())) {
            // If ride is not already full,
            if (!$ride->isFull()) {
                $ride->join(getConnectedUser());
                echo json_encode(['success' => $ride->name. 'にエントリーしました！楽しいツアーになることは間違いありません！']);
            } else json_encode(['error' => $ride->name. 'が既に定員に達成しています。']);
        } else json_encode(['error' => $ride->name. 'に既にエントリーしています。']);
    }

}