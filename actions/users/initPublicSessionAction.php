<?php

session_start();
if (isset($_SESSION['id']) && $_SESSION['id'] > 0) $connected_user = new User($_SESSION['id']);
else $connected_user = false; ?>