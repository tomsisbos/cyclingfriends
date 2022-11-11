<?php
session_start();
// Autoload
require_once $_SERVER["DOCUMENT_ROOT"] . '/class/Autoloader.php'; 
Autoloader::register(); 
require $_SERVER["DOCUMENT_ROOT"] . '/includes/functions.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/users/securityAction.php';
require $_SERVER["DOCUMENT_ROOT"] . '/actions/databaseAction.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET)) {
        
        if (isset($_GET['getActivities'])) {

            $parameters = explode(',', $_GET['getActivities']);
            $limit = $parameters[0];
            $offset = $parameters[1];
            $preview_photos_quantity = $parameters[2];
            $results = $connected_user->getPublicActivities($offset, $limit);
            $activities = [];
            foreach ($results as $result) {
                $activity = new Activity($result['id']);
                $activity->datetimeString = $activity->datetime->format('Y/m/d');
                $activity->photosNumber = count($activity->getPhotoIds());
                $activity->checkpoints = $activity->getCheckpoints();
                $activity->photos = $activity->getPreviewPhotos($preview_photos_quantity);
                if (substr($activity->duration->format('H'), 0, 1) == '0') $activity->formattedDuration = substr($activity->duration->format('H'), 1, strlen($activity->duration->format('H')));
                else $activity->formattedDuration = $activity->duration->format('H') . '<span class="ac-spec-unit"> h </span>' . $activity->duration->format('i');
                array_push($activities, $activity);
            }

            echo json_encode($activities);

        }

    }

}