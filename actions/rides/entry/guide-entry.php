<?php

$ride = new Ride($params['ride_id']);
$guide = new Guide($params['guide_id'], $ride->id);

if (isset($_POST)) {

    $updated = false;
    foreach ($ride->getAdditionalFields() as $a_field) {
        if (isset($_POST['a_field_' .$a_field->id])) {
            $a_field->setAnswer($guide->id, $_POST['a_field_' .$a_field->id. '_type'], $_POST['a_field_' .$a_field->id]);
            $updated = true;
        };
    }

    if ($updated) $successmessage = "エントリー情報が更新されました！";
}