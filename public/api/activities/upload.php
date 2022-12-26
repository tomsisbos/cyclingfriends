<?php

require '../../../includes/api-head.php';

use phpGPX\phpGPX;

if (isset($_FILES['activity'])) {

    define('ALLOWED_EXTENSIONS', ['gpx', 'tcx', 'fit']);
    $ext = checkFileExtension(ALLOWED_EXTENSIONS, $_FILES['activity']['name']);

    if ($ext) {

        // Create temp folder if necessary
        if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data')) mkdir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data');
        if (!is_dir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data/temp')) mkdir($_SERVER["DOCUMENT_ROOT"] . '/media/activities/data/temp');
        
        // Move uploaded file to activity temp folder
        $url = $_SERVER["DOCUMENT_ROOT"] . '/media/activities/data/temp/temp.' . $ext;
        move_uploaded_file($_FILES['activity']['tmp_name'], $url);
        
        // Load parser dependancies
        $folder = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
        require $folder . '/vendor/autoload.php';  // this file is in the project's root folder

        if ($ext == 'gpx') {

            /*echo json_encode(['success' => 'File has been correctly uploaded.', 'filetype' => 'gpx', 'file' => file_get_contents($url)], JSON_INVALID_UTF8_SUBSTITUTE);
            exit;*/
            $gpx = new phpGPX();
            $file = $gpx->load($url);
                
            echo json_encode(['success' => 'アップロードが完了しました。', 'filetype' => 'gpx', 'file' => $file], JSON_INVALID_UTF8_SUBSTITUTE);
        
        } else if ($ext == 'fit') {

            $pFFA = new adriangibbons\phpFITFileAnalysis($url);
            $data = new FitData($pFFA->data_mesgs);

            echo json_encode(['success' => 'アップロードが完了しました。', 'filetype' => 'fit', 'file' => $data], JSON_INVALID_UTF8_SUBSTITUTE);

        } else if ($ext == 'tcx') {

            echo json_encode(['error' => '*.tcx形式ファイルはまだ対応しておりません。ご迷惑をお掛け致します。']);

        }

    } else {

        // Build extensions list
        $extensions_list = '';
        foreach (ALLOWED_EXTENSIONS as $allowed_extension) $extensions_list .= $allowed_extension . ', ';
        $extensions_list = substr($extensions_list, 0, strlen($extensions_list) - 2);
        echo json_encode(['error' => 'この形式のファイルはアップロードできません。アップロードできるファイル形式は次の通り：' . $extensions_list]);
    }

}