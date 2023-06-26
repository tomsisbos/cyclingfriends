<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if ($_GET['request-type'] == 'record') {
        $loading_record = new LoadingRecord(getConnectedUser()->id, $_GET['entry-table'], $_GET['entry-id']);
        echo json_encode($loading_record->getStatus());
    }

    else if ($_GET['request-type'] == 'next-entry-id') {
        echo json_encode(getNextAutoIncrement('activities'));
    }

}