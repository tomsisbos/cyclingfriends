<?php

if (isset($_SESSION['ride_entry_data_' .$ride->id])) {

    $entry_data = $_SESSION['ride_entry_data_' .$ride->id];

    // Update user information
    foreach ($entry_data as $key => $value) if (substr($key, 0, 8) !== 'a_field_') getConnectedUser()->update($key, $value);
    foreach ($ride->getAdditionalFields() as $a_field) $a_field->setAnswer(getConnectedUser()->id, $entry_data['a_field_' .$a_field->id. '_type'], $entry_data['a_field_' .$a_field->id]);

    // Join ride
    $ride->join(getConnectedUser());

    // Clear session variable
    unset($_SESSION['ride_entry_data_' .$ride->id]);

} else header('location:' .$router->generate('ride-participations'));