<?php

if (isset($_SESSION['ride_entry_data_' .$ride->id])) unset($_SESSION['ride_entry_data_' .$ride->id]);

// Email
$entry_data['email'] = getConnectedUser()->email;

// Name
if (empty(getConnectedUser()->first_name) || empty(getConnectedUser()->last_name)) {
    $entry_data['last_name'] = $_POST['last_name'];
    $entry_data['first_name'] = $_POST['first_name'];
} else {
    $entry_data['last_name'] = getConnectedUser()->last_name;
    $entry_data['first_name'] = getConnectedUser()->first_name;
}

// Birthdate
if (!getConnectedUser()->birthdate) $entry_data['birthdate'] = $_POST['birthdate'];
else $entry_data['birthdate'] = getConnectedUser()->birthdate;

// Emergency number
if (!getConnectedUser()->emergency_number) $entry_data['emergency_number'] = $_POST['emergency_number'];
else $entry_data['emergency_number'] = getConnectedUser()->emergency_number;

// Additional fields
$a_fields = $ride->getAdditionalFields();
foreach ($a_fields as $a_field) {
    if (!isset($_POST['a_field_' .$a_field->id])) header('location: ' .$router->generate('ride-entry', ['ride_id' => $ride->id])); // If necessary additional fields info is not set, redirect to the entry page
    $entry_data['a_field_' .$a_field->id] = $_POST['a_field_' .$a_field->id];
    $entry_data['a_field_' .$a_field->id. '_type'] = $_POST['a_field_' .$a_field->id. '_type'];
}

// Rental bike
if ($ride->getRentalBikeEntry(getConnectedUser()->id)) $entry_data['rental_bike'] = $ride->getRentalBikeEntry(getConnectedUser()->id)->id;
else $entry_data['rental_bike'] = $_POST['rental_bike'];