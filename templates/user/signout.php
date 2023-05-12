<?php
session_start();
$_SESSION = [];
session_destroy();
if (!empty($params) && isset($params['url'])) header('location: ' .$params['url']);
else header('location: /signin');

?>