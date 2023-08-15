<?php

if (getConnectedUser() && $ride->isFree() && isset($_POST['free'])) {
    
    include '../includes/rides/entry/treat-entry-data.php';
    
    // Update user information
    foreach ($entry_data as $key => $value) if (substr($key, 0, 8) !== 'a_field_') getConnectedUser()->update($key, $value);
    foreach ($ride->getAdditionalFields() as $a_field) $a_field->setAnswer(getConnectedUser()->id, $entry_data['a_field_' .$a_field->id. '_type'], $entry_data['a_field_' .$a_field->id]);

    // Join ride
    $ride->join(getConnectedUser());

    $_SESSION['successmessage'] = $ride->name. 'にエントリーしました！楽しいツアーになることは間違いありません！';

    header('location: ' .$router->generate('ride-single', ['ride_id' => $ride->id]));

}