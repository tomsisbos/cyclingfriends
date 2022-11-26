<?php

if (!isset($_SESSION['auth'])) header('location: /signin');
else $connected_user = new User($_SESSION['id']); ?>