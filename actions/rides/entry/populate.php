<?php

$entry_data = [];

// Pre populate entry data using session (if coming back from further screen)
if (isset($_SESSION['ride_entry_data_' .$ride->id])) $entry_data = $_SESSION['ride_entry_data_' .$ride->id];

// Pre populate entry data using post global variable (if newly create account)
else if (!empty($_POST)) {
    $entry_data = [
        'email' => $_POST['email'],
        'login' => $_POST['login'],
        'password' => $_POST['password'],
        'last_name' => $_POST['last_name'],
        'first_name' => $_POST['first_name'],
        'birthdate' => $_POST['birthdate'],
    ];
    foreach ($ride->getAdditionalFields() as $a_field) if (isset($_POST['a_field_' .$a_field->id])) $entry_data['a_field_' .$a_field->id] = $_POST['a_field_' .$a_field->id];
}