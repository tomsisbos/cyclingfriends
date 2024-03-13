<?php

$base_directory = substr($_SERVER['DOCUMENT_ROOT'], 0, - strlen(basename($_SERVER['DOCUMENT_ROOT'])));
require_once $base_directory . '/vendor/autoload.php';
require_once $base_directory . '/class/CFAutoloader.php';
CFAutoloader::register();
require_once $base_directory . '/includes/functions.php';

use Stoufa\GarminApi\GarminApi;


// When receiving a ping request from Garmin server
$json = file_get_contents('php://input'); // Get json file from xhr request
$data = json_decode($json, true);


foreach ($data['activityFiles'] as $activity_files) {

    $garmin = new Garmin($activity_files['userId']);
    $result = $garmin->populateUserTokens();

    // Condition to ensure that no matching of userId would not stop all activity syncs
    if ($result) {

        // Save all ping calls logs in a json file
        $temp_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/temp';
        if (!file_exists($temp_directory)) mkdir($temp_directory, 0777, true); // Create user directory if necessary
        $temp_url = $temp_directory. '/' .$activity_files['summaryId']. '.json';
        file_put_contents($temp_url, $json);

        // Prepare parameters
        $parsed = parse_url($activity_files['callbackURL']);
        parse_str($parsed['query'], $params);
        $id = $params['id'];
        $token = $params['token'];
        $ext = strtolower($activity_files['fileType']);

        // Retrieve corresponding activity details
        $activity_file = $garmin->retrieveActivityFile($id, $token, [
            'ext' => $ext,
            'garmin_activity_id' => intval($activity_files['activityId']), 
            'garmin_user_id' => $activity_files['userId']
        ]);
        
        // Parse file

        $user_id = $activity_file->getUserIdFromGarminId($activity_files['userId']);

        try {

            $activity_data = $activity_file->parse();
            
            // Only continue if activity doesn't already exist
            if (!$activity_data->alreadyExists($user_id)) {

                // Create activity
                $activity_id = $activity_data->createActivity($user_id, ['title' => isset($activity_files['activityName']) ? $activity_files['activityName'] : 'activity']);

                // Send a notification
                $activity = new Activity($activity_id);
                $activity->notify($user_id, 'new_synced_activity');

            } else throw new Exception('already_exists', $activity_data->alreadyExists($user_id));

        // If error has occured during parsing, abort and send a notification
        } catch (Exception $e) {

            $errors_directory = $_SERVER["DOCUMENT_ROOT"]. '/api/garmin/errors';
            if (!file_exists($errors_directory)) mkdir($errors_directory, 0777, true); // Create user directory if necessary
            $temp_url = $errors_directory. '/' .'error-userid-' . $user_id . '-garminactivityid-' . $activity_files['activityId'] . '.log';
            file_put_contents($temp_url, 'Garmin Connectよりアクティビティ「' .$activity_files['activityId']. '」を同期しようとしたところ、次の通りエラーが発生しました：' .$e->getMessage(). ', id: ' .$e->getCode());

            $user = new User($user_id);
            if ($e->getMessage() == 'missing_coordinates') $user->notify($user_id, 'new_synced_activity_error_missing_coordinates');
            else if ($e->getMessage() == 'file_not_found') $user->notify($user_id, 'new_synced_activity_file_not_found');
            else if ($e->getMessage() == 'no_record_data') $user->notify($user_id, 'new_synced_activity_error_missing_record');
            else if ($e->getMessage() == 'already_exists') return;
            else {
                $user->notify($user_id, 'new_synced_activity_othererror_' . $e->getMessage());
            }
        }
    }

    // Retrieve a 200 response code
    http_response_code(200);
}