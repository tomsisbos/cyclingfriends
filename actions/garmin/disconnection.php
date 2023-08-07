<?php

if (isset($_POST['garmin_disconnect'])) {
    $garmin = getConnectedUser()->getGarmin();
    $garmin->deregister();

    $successmessage = "Garmin Connectとの接続が解除されました。";
}