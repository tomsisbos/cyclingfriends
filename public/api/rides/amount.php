<?php

require '../../../includes/api-head.php';

if (isset($_GET['ride'])) {
    $ride = new Ride($_GET['ride']);

    if (isset($_GET['fieldId'])) {
        $a_field = new AdditionalField($_GET['fieldId']);
        $a_field->setAnswer(getConnectedUser()->id, 'product', $_GET['optionId']);
    }

    if (isset($_GET['rentalBikeId'])) {
        if ($_GET['rentalBikeId'] == 'none') $ride->removeRentalBikeEntry(getConnectedUser()->id);
        else $ride->setRentalBikeEntry(getConnectedUser()->id, $_GET['rentalBikeId']);
    }

    echo json_encode($ride->calculateAmount(getConnectedUser()->id));
}
