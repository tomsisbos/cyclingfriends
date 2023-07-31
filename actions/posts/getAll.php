<?php

require '../actions/database.php';

$query = "SELECT id FROM posts ORDER BY datetime DESC";
$getResultsNumber = $db->prepare($query);
$getResultsNumber->execute();
if (isset($offset) && isset($limit)) $query .= " LIMIT {$limit} OFFSET {$offset}";
$getPosts = $db->prepare($query);
$getPosts->execute();
