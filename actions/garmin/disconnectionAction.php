<?php

if (isset($_POST['garmin_disconnect'])) {
    $garmin = $connected_user->getGarmin();
    $garmin->disconnect();

    $successmessage = "Garmin Connectとの接続が解除されました。";
}