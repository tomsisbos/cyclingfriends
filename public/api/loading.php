<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['record-type'])) {
        $loading_record = new LoadingRecord($connected_user->id, $_GET['record-type']);
        $loading_record->setAsLastEntryId();
        echo json_encode($loading_record->getStatus());
    }

}