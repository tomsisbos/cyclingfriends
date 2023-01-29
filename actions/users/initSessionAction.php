<?php

session_start();

if (!isset($_SESSION['auth']) || $_SESSION['auth'] != true) header('location: /signin');
else $connected_user = new User($_SESSION['id']); ?>