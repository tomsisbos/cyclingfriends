<?php

require '../../../includes/api-head.php';

// On activity upload
if (isset($_FILES['activity'])) {
    
    $activity_file = new ActivityFile();

    // Filter file extensions
    $ext = checkFileExtension($activity_file->allowed_extensions, $_FILES['activity']['name']);
    if ($ext) {

        // Upload activity file on server
        $metadata = [
            'user_id' => getConnectedUser()->id,
            'ext' => $ext
        ];
        $activity_file->create(file_get_contents($_FILES['activity']['tmp_name']), $metadata);
        
        // Parse data and send it back to client with a message to display
        try {
            $activity_data = $activity_file->parse();
            $activity_data->sceneries = $activity_data->linestring->getCloseSceneries(1000);
            echo json_encode(['success' => 'アップロードが完了しました。', 'activityData' => $activity_data], JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()], JSON_INVALID_UTF8_SUBSTITUTE);
        }

    // If file extension is not supported
    } else {

        // Build extensions list
        $extensions_list = '';
        foreach ($activity_file->allowed_extensions as $allowed_extension) $extensions_list .= $allowed_extension . ', ';
        $extensions_list = substr($extensions_list, 0, strlen($extensions_list) - 2);
        echo json_encode(['error' => 'この形式のファイルはアップロードできません。アップロードできるファイル形式は次の通り：' . $extensions_list]);
    }

}