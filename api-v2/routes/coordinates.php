<?php

header('Content-Type: application/json, charset=UTF-8');

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/actions/users/initPublicSession.php';
require_once $base_directory . '/includes/functions.php';
require_once $base_directory . '/actions/database.php';

$route = new Route($_GET['id']);
echo json_encode($route->getLinestring());