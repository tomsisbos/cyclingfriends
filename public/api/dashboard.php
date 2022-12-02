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
        $entries = [];
        foreach ($results as $result) {
            // Activity data preparation
            if ($result->type === 'activity') {
                $result->datetimeString = $result->datetime->format('Y/m/d');
                $result->photosNumber = count($result->getPhotoIds());
                $result->checkpoints = $result->getCheckpoints();
                $result->routeThumbnail = $result->route->getThumbnail();
                $result->photos = $result->getPreviewPhotos($preview_photos_quantity);
                if (substr($result->duration->format('H'), 0, 1) == '0') $result->formattedDuration = substr($result->duration->format('H'), 1, strlen($result->duration->format('i'))) . '<span class="ac-spec-unit"> h </span>' . $result->duration->format('i');
                else $result->formattedDuration = $result->duration->format('H') . '<span class="ac-spec-unit"> h </span>' . $result->duration->format('i');
                $result->averagespeed = $result->getAverageSpeed();
                $result->propic = $result->user->getPropicElement();
                array_push($entries, $result);
            // Mkpoint data preparation
            } else if ($result->type === 'mkpoint') {
                $result->propic = $result->user->getPropicElement();
                $result->featuredimage = $result->getImages()[0]->blob;
                array_push($entries, $result);
            }
        }

        echo json_encode($entries);

    }

}