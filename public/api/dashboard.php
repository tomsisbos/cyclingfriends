<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {
        
    if (isset($_GET['getThread'])) {

        $parameters = explode(',', $_GET['getThread']);
        $limit = $parameters[0];
        $offset = $parameters[1];
        $preview_photos_quantity = $parameters[2];
        $results = $connected_user->getThread($offset, $limit);
        $activities = [];
        foreach ($results as $result) {
            if ($result->type === 'activity') {
                $result->datetimeString = $result->datetime->format('Y/m/d');
                $result->photosNumber = count($result->getPhotoIds());
                $result->checkpoints = $result->getCheckpoints();
                $result->photos = $result->getPreviewPhotos($preview_photos_quantity);
                if (substr($result->duration->format('H'), 0, 1) == '0') $result->formattedDuration = substr($result->duration->format('H'), 1, strlen($result->duration->format('i'))) . '<span class="ac-spec-unit"> h </span>' . $result->duration->format('i');
                else $result->formattedDuration = $result->duration->format('H') . '<span class="ac-spec-unit"> h </span>' . $result->duration->format('i');
                $result->averagespeed = $result->getAverageSpeed();
                $result->propic = $result->user->getPropicElement();
                array_push($activities, $result);
            }
        }

        echo json_encode($activities);

    }

}