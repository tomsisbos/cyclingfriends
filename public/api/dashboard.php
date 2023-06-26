<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {
        
    if (isset($_GET['getThread'])) {

        $parameters = explode(',', $_GET['getThread']);
        $limit = $parameters[0];
        $offset = $parameters[1];
        $preview_photos_quantity = $parameters[2];
        $results = getConnectedUser()->getThread($offset, $limit);
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
                if (substr($activity->duration->h, 0, 1) == '0') $activity->formattedDuration = substr($activity->duration->h, 1, strlen($activity->duration->i)) . '<span class="ac-spec-unit"> h </span>' . $activity->duration->i;
                else $activity->formattedDuration = $activity->duration->h . '<span class="ac-spec-unit"> h </span>' . $activity->duration->i;
                $activity->averagespeed = $activity->getAverageSpeed();
                $activity->user_login = $activity->getAuthor()->login;
                $activity->propic = $activity->getAuthor()->getPropicHTML();
                array_push($entries, $activity);
            // Scenery data preparation
            } else if ($result['type'] === 'scenery') {
                $scenery = new Scenery($result['id']);
                $scenery->type = 'scenery';
                $scenery->cleared = $scenery->isCleared();
                $scenery->user_login = $scenery->getAuthor()->login;
                $scenery->propic = $scenery->getAuthor()->getPropicHTML();
                $scenery->featuredimageUrl = $scenery->getImages()[0]->url;
                array_push($entries, $scenery);
            }
        }

        echo json_encode($entries);

    }

}