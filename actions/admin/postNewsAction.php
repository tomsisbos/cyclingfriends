<?php

// Post news
if (!empty($_POST)) {
    $new = new DevNew();
    $new->create($_POST['title'], $_POST['type'], $_POST['content']);
}