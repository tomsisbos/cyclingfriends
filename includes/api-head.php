<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/class/Autoloader.php'; 
Autoloader::register(); 
require $base_directory . '/includes/functions.php';
require $base_directory . '/actions/users/initSessionAction.php';
require $base_directory . '/actions/databaseAction.php';