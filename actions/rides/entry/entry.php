<?php

if (!getConnectedUser() || !$ride->isParticipating(getConnectedUser())) {

    $entry_data = [];

    // Pre populate entry data using session (if coming back from further screen)
    if (isset($_SESSION['ride_entry_data_' .$ride->id])) $entry_data = $_SESSION['ride_entry_data_' .$ride->id];

    // Pre populate entry data using post global variable (if newly create account)
    else if (!empty($_POST) && !isset($_POST['free'])) {
        $entry_data = [
            'email' => $_POST['email'],
            'login' => $_POST['login'],
            'password' => $_POST['password'],
            'last_name' => $_POST['last_name'],
            'first_name' => $_POST['first_name'],
            'birthdate' => $_POST['birthdate'],
            'emergency_number' => $_POST['emergency_number']
        ];
        foreach ($ride->getAdditionalFields() as $a_field) if (isset($_POST['a_field_' .$a_field->id])) $entry_data['a_field_' .$a_field->id] = $_POST['a_field_' .$a_field->id];
    }

} else {

    $_SESSION['errormessage'] = 'このツアーに既にエントリーしています。';
    header('location: ' .$router->generate('ride-single', ['ride_id' => $ride->id]));

}