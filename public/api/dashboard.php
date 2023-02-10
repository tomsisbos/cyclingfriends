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
            if ($result['type'] === 'activity') {
                $activity = new Activity($result['id']);
                $activity->type = 'activity';
                $activity->datetimeString = $activity->datetime->format('Y/m/d');
                $activity->photosNumber = count($activity->getPhotoIds());
                $activity->checkpoints = $activity->getCheckpoints();
                $activity->routeThumbnail = $activity->route->getThumbnail();
                $activity->photos = $activity->getPreviewPhotos($preview_photos_quantity);
                if (substr($activity->duration->format('H'), 0, 1) == '0') $activity->formattedDuration = substr($activity->duration->format('H'), 1, strlen($activity->duration->format('i'))) . '<span class="ac-spec-unit"> h </span>' . $activity->duration->format('i');
                else $activity->formattedDuration = $activity->duration->format('H') . '<span class="ac-spec-unit"> h </span>' . $activity->duration->format('i');
                $activity->averagespeed = $activity->getAverageSpeed();
                $activity->user_login = $activity->getAuthor()->login;
                $activity->propic = $activity->getAuthor()->getPropicElement();
                array_push($entries, $activity);
            // Mkpoint data preparation
            } else if ($result['type'] === 'mkpoint') {
                $mkpoint = new Mkpoint($result['id']);
                $mkpoint->type = 'mkpoint';
                $mkpoint->cleared = $mkpoint->isCleared();
                $mkpoint->user_login = $mkpoint->getAuthor()->login;
                $mkpoint->propic = $mkpoint->getAuthor()->getPropicElement();
                $mkpoint->featuredimageUrl = $mkpoint->getImages()[0]->url;
                array_push($entries, $mkpoint);
            }
        }

        echo json_encode($entries);

    }

}