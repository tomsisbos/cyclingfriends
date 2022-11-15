<?php

if (!isset($_SESSION['auth'])) header('location: ../../signin.php');
else $connected_user = new User($_SESSION['id']); ?>