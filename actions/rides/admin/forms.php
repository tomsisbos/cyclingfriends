<?php

// If data has been posted
if (isset($_POST) && isset($_POST['add'])) {

    var_dump($_POST);
    // Set variables
    $question = htmlspecialchars($_POST[$_POST['type'] . '_question']);
    $type = $_POST['type'];

    // If does not already exist, insert data in table
    $checkIfAlreadyExists = $db->prepare('SELECT question FROM ride_additional_fields WHERE ride_id = ? AND question = ? AND type = ?');
    $checkIfAlreadyExists->execute(array($ride->id, $question, $type));
    if ($checkIfAlreadyExists->rowCount() == 0) {
        $insertField = $db->prepare('INSERT INTO ride_additional_fields(ride_id, question, type) VALUES (?, ?, ?)');
        $insertField->execute(array($ride->id, $question, $type));
    }

    // Get newly created field id
    $getFieldId = $db->prepare('SELECT id FROM ride_additional_fields WHERE ride_id = ? AND question = ? AND type = ?');
    $getFieldId->execute(array($ride->id, $question, $type));
    $field_id = $getFieldId->fetch(PDO::FETCH_NUM)[0];

    // For select fields, insert options in table
    if ($type == 'select') {
        $number = 1;
        while (isset($_POST['select_option_' . $number]) AND !empty($_POST['select_option_' . $number])) {
            $checkIfAlreadyExists = $db->prepare('SELECT content FROM ride_additional_field_options WHERE field_id = ? AND number = ?');
            $checkIfAlreadyExists->execute(array($field_id, $number));
            if ($checkIfAlreadyExists->fetch()[0] != $_POST['select_option_' . $number]) {
                $content = $_POST['select_option_' . $number];
                $insertField = $db->prepare('INSERT INTO ride_additional_field_options(field_id, number, content) VALUES (?, ?, ?)');
                $insertField->execute(array($field_id, $number, $content));
            }
            $number++;
        }
    }

}

// If deleted button has been pressed
if (isset($_POST) && isset($_POST['delete'])) {
    $field = new AdditionalField($_POST['delete']);
    $field->delete();
}