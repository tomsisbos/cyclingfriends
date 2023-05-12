<?php

session_start();

if (!isset($_SESSION['auth']) || $_SESSION['auth'] != true) header('location: ' .$_SERVER['REQUEST_URI']. '/signin');
else $connected_user = new User($_SESSION['id']); ?>