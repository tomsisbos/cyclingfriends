<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['toggle-scenery'])) {
        $scenery = new Scenery($_GET['toggle-scenery']);
        $response = $scenery->toggleFavorites();
        echo json_encode($response);
    }

    if (isset($_GET['toggle-segment'])) {
        $segment = new Segment($_GET['toggle-segment']);
        $response = $segment->toggleFavorites();
        echo json_encode($response);
    }

}