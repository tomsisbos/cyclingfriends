<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/includes/api-authentication.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // In case a Json request have been detected
    $json = file_get_contents('php://input');
    $data = json_decode($json);

    $grade       = $data->grade;
    $object_type = $data->object_type;
    $object_id   = $data->object_id;
    $user_id     = $user->id;

    $hasUserAlreadyRated = $db->prepare("SELECT id FROM {$object_type}_grades WHERE user_id = ? AND {$object_type}_id = ?");
    $hasUserAlreadyRated->execute([$user_id, $object_id]);
    if ($hasUserAlreadyRated->rowCount() > 0) {
        $removeGrade = $db->prepare("DELETE FROM {$object_type}_grades WHERE user_id = ? AND {$object_type}_id = ?");
        $removeGrade->execute([$user_id, $object_id]);
    } else {
        $insertGrade = $db->prepare("INSERT INTO {$object_type}_grades (user_id, {$object_type}_id, grade) VALUES (?, ?, ?) ");
        $insertGrade->execute([$user_id, $object_id, $grade]);
    }

    return true;

}