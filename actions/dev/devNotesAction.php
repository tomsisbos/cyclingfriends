<?php

include '../actions/databaseAction.php';

// If min date filter is empty, set it as current date
if (empty($_POST['filter_date_min'])) $_POST['filter_date_min'] = date('1970-01-01');
		
// If max date filter is empty, set it as 2099-12-31
if (empty($_POST['filter_date_max'])) $_POST['filter_date_max'] = date('2099-12-31');

if (!isset($_POST['filter_type'])) $_POST['filter_type'] = 'all';

if (!isset($_POST['filter_search'])) $_POST['filter_search'] = '';

$query = "SELECT id FROM dev_notes WHERE
time BETWEEN :datemin AND :datemax
    AND
        (CASE 
            WHEN :type = 'all' THEN :type = :type
            ELSE type = :type
        END)
    AND title LIKE '%' :search '%'
    ORDER BY time DESC";
$params = [':datemin' => $_POST['filter_date_min'], 'datemax' => $_POST['filter_date_max'], ':type' => $_POST['filter_type'], ':search' => $_POST['filter_search']];

$getDevNotes = $db->prepare($query. " LIMIT " .$offset. ", " .$limit);
$getDevNotes->execute($params);
$dev_notes = [];

while ($id = $getDevNotes->fetch(PDO::FETCH_COLUMN)) array_push($dev_notes, new DevNote($id));