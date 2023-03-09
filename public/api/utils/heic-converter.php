
<?php

require '../../../includes/api-head.php';

if (isset($_FILES['file'])) {
    $file = $_FILES['file']['tmp_name'];
    $temp_path = $_SERVER["DOCUMENT_ROOT"]. "/media/temp/". $_FILES['file']['name'];
    move_uploaded_file($file, $_SERVER["DOCUMENT_ROOT"]. "/media/temp/". $_FILES['file']['name']);
    $relative_path = "/media/temp/converted_from_heic.jpg";
    $jpg = Maestroerror\HeicToJpg::convert($temp_path)->saveAs($_SERVER["DOCUMENT_ROOT"] . $relative_path);
    echo json_encode(['path' => $relative_path]);
    exit;
}