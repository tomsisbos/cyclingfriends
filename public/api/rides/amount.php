<?php

require '../../../includes/api-head.php';

if (isset($_GET['ride'])) {
    $ride = new Ride($_GET['ride']);

    if (isset($_GET['fieldId'])) {
        $a_field = new AdditionalField($_GET['fieldId']);
        $a_field->setAnswer(getConnectedUser()->id, 'product', $_GET['optionId']);
    }

    echo json_encode($ride->calculateAmount(getConnectedUser()->id));
}
