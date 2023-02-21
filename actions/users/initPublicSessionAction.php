<?php

session_start();
if (isset($_SESSION['id'])) $connected_user = new User($_SESSION['id']); ?>