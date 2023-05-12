<?php

// if is set inside direct variable
if (isset($errormessage)) echo '<div class="error-block" style="margin: 0px;"><p class="error-message">' .$errormessage. '</p></div>';
if (isset($successmessage)) echo '<div class="success-block" style="margin: 0px;"><p class="success-message">' .$successmessage. '</p></div>';
