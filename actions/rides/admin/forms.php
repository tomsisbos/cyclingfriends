<?php

// If data has been posted
if (isset($_POST) && isset($_POST['add'])) {

    // Set variables
    $question = htmlspecialchars($_POST[$_POST['type'] . '_question']);
    $type = $_POST['type'];

    // If does not already exist, insert data in table
    $checkIfAlreadyExists = $db->prepare('SELECT question FROM ride_additional_fields WHERE ride_id = ? AND question = ? AND type = ?');
    $checkIfAlreadyExists->execute(array($ride->id, $question, $type));
    if ($checkIfAlreadyExists->rowCount() == 0) {
        $insertField = $db->prepare('INSERT INTO ride_additional_fields(ride_id, question, type) VALUES (?, ?, ?)');
        $insertField->execute(array($ride->id, $question, $type));

        // Get newly created field id
        $getFieldId = $db->prepare('SELECT id FROM ride_additional_fields WHERE ride_id = ? AND question = ? AND type = ?');
        $getFieldId->execute(array($ride->id, $question, $type));
        $field_id = $getFieldId->fetch(PDO::FETCH_NUM)[0];

        // For select fields, insert options in table
        if ($type == 'select' || $type = 'product') {
            $field = new AdditionalField($field_id);
            $number = 1;
            $options = [];
            $prices = [];
            while (isset($_POST['select_option_' . $number]) AND !empty($_POST['select_option_' . $number])) {
                $option = htmlspecialchars($_POST['select_option_' . $number]);
                array_push($options, $option);
                if ($type == 'product') {
                    $price = intval(htmlspecialchars($_POST['select_price_' . $number]));
                    if (empty($price)) $price = 0;
                    array_push($prices, $price);
                }
                $number++;
            }
            $field->setOptions($options, $prices);
        }

        $successmessage = "質問が追加されました！";

    } else $errormessage = "この質問は既に追加されています。";
}

// If entry has been edited
if (isset($_POST) && isset($_POST['editSave'])) {
    
    // Set variables
    $question = htmlspecialchars($_POST['question']);
    $type = $_POST['type'];

    // Update field
    $field = new AdditionalField($_POST['editSave']);
    $field->update($type, $question);

    // Update field options
    $options_number = 1;
    $options = [];
    $prices = [];
    while (isset($_POST['select_option_' . $options_number]) AND !empty($_POST['select_option_' . $options_number])) {
        $option = htmlspecialchars($_POST['select_option_' . $options_number]);
        array_push($options, $option);
        if ($type == 'product') {
            $price = intval(htmlspecialchars($_POST['select_price_' . $options_number]));
            if (empty($price)) $price = 0;
            array_push($prices, $price);
        }
        $options_number++;
    }
    $field->updateOptions($options, $prices);
    
    $successmessage = "質問の変更が保存されました！";
}

// If deleted button has been pressed
if (isset($_POST) && isset($_POST['delete'])) {
    $field = new AdditionalField($_POST['delete']);
    $field->delete();
    $successmessage = "質問が削除されました。";
}