<?php

// If is set inside session variable
if (isset($_SESSION['errormessage'])) {
    $errormessage = $_SESSION['errormessage'];
    unset($_SESSION['errormessage']);
} else if (isset($_SESSION['successmessage'])) {
    $successmessage = $_SESSION['successmessage'];
    unset($_SESSION['successmessage']);
}