<?php
session_start();
require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php'; 
Autoloader::register();
require $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

if (isset($_FILES['activity'])) {

    define('ALLOWED_EXTENSIONS', ['gpx', 'tcx', 'fit']);
    $ext = checkFileExtension(ALLOWED_EXTENSIONS, $_FILES['activity']['name']);

    if ($ext) {

        // Create temp folder if necessary
        if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/activities/data')) mkdir($_SERVER["DOCUMENT_ROOT"] . '/activities/data');
        if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/activities/data/temp')) mkdir($_SERVER["DOCUMENT_ROOT"] . '/activities/data/temp');
        
        // Move uploaded file to activity temp folder
        $url = $_SERVER["DOCUMENT_ROOT"] . '/activities/data/temp/temp.' . $ext;
        move_uploaded_file($_FILES['activity']['tmp_name'], $url);

        if ($ext == 'gpx') {

            echo json_encode(['success' => 'File has been correctly uploaded.', 'filetype' => 'gpx', 'file' => file_get_contents($url)]);
            exit;
        
        } else if ($ext == 'fit') {

            require $_SERVER["DOCUMENT_ROOT"] . '/vendor/autoload.php';  // this file is in the project's root folder
            $pFFA = new adriangibbons\phpFITFileAnalysis($url);

            echo json_encode(['success' => 'File has been correctly uploaded.', 'filetype' => 'fit', 'file' => $pFFA->data_mesgs]);

        } else if ($ext == 'tcx') {

            echo json_encode(['error' => 'Sorry, we don\'t support *.tcx files yet.']);

        }

    } else {

        // Build extensions list
        $extensions_list = '';
        foreach (ALLOWED_EXTENSIONS as $allowed_extension) $extensions_list .= $allowed_extension . ', ';
        $extensions_list = substr($extensions_list, 0, strlen($extensions_list) - 2);
        echo json_encode(['error' => 'This file format can not be uploaded. Only ' . $extensions_list . ' formats are allowed.']);
    }

}