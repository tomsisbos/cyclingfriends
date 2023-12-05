<?php

header('Content-Type: application/json, charset=UTF-8');

require '../includes/api-head.php';

if (isSessionActive()) echo json_encode($_SESSION);
else echo json_encode(false);