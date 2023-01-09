<?php

// If data has been posted
if (isset($_POST) && !empty($_POST)) {

    // Set variables
    var_dump($_POST);
    $question = htmlspecialchars($_POST[$_POST['type'] . '_question']);
    $type = $_POST['type'];

    // If does not already exist, insert data in table
    $checkIfAlreadyExists = $db->prepare('SELECT question FROM ride_additional_fields WHERE ride_id = ? AND question = ? AND type = ?');
    $checkIfAlreadyExists->execute(array($ride->id, $question, $type));
    if ($checkIfAlreadyExists->rowCount() == 0) {
        $insertField = $db->prepare('INSERT INTO ride_additional_fields(ride_id, question, type) VALUES (?, ?, ?)');
        $insertField->execute(array($ride->id, $question, $type));
    }

}

// If additional fields are registered for this ride, get them
$getFields = $db->prepare('SELECT id, question, type FROM ride_additional_fields WHERE ride_id = ?');
$getFields->execute(array($ride->id));
if ($getFields->rowCount() > 0) $additional_fields = $getFields->fetchAll(PDO::FETCH_ASSOC);