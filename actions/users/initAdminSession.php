<?php

session_start();

if (!isset($_SESSION['auth']) || $_SESSION['auth'] != true || !getConnectedUser()->hasAdministratorRights()) header('location: ' .$_SERVER['REQUEST_SCHEME']. '://' .$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']. '/signin'); ?>