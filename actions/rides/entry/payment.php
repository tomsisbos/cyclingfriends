<?php

if (!$ride->isParticipating(getConnectedUser())) {

    include '../includes/rides/entry/treat-entry-data.php';

    $_SESSION['ride_entry_data_' .$ride->id] = $entry_data;

} else {

    $_SESSION['errormessage'] = 'このツアーに既にエントリーしています。';
    header('location: ' .$router->generate('ride-single', ['ride_id' => $ride->id]));
    
}