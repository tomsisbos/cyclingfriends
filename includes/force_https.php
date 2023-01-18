<?php

$is_https = false;
if (isset($_SERVER['HTTPS'])) $is_https=$_SERVER['HTTPS'];
if ($is_https !== "on") {
    header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    exit(1);
}