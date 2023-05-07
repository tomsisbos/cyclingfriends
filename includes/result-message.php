<?php

// If is set inside session variable
if (isset($_SESSION['errormessage'])) {
    $errormessage = $_SESSION['errormessage'];
    unset($_SESSION['errormessage']);
} else if (isset($_SESSION['successmessage'])) {
    $successmessage = $_SESSION['successmessage'];
    unset($_SESSION['successmessage']);
}

// if is set inside direct variable
if (isset($errormessage)) echo '<div class="error-block" style="margin: 0px;"><p class="error-message">' .$errormessage. '</p></div>';
if (isset($successmessage)) echo '<div class="success-block" style="margin: 0px;"><p class="success-message">' .$successmessage. '</p></div>';
