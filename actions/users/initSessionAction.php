<?php

session_start();

if (!isSessionActive() || $_SESSION['auth'] != true) header('location: ' .$_SERVER['REQUEST_URI']. '/signin'); ?>