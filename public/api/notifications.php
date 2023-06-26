<?php

require '../../includes/api-head.php';

// In case an Ajax request have been detected
if (isAjax()) {

    if (isset($_GET['get'])) {

        if (isset($_GET['reset'])) {
            $notifications = getConnectedUser()->getNotifications();
            setcookie("loadedNotificationsNumber", "10", time() + 86400, "/" );

        } else {
            if (isset($_COOKIE["loadedNotificationsNumber"])) $loaded_number = intval($_COOKIE["loadedNotificationsNumber"]);
            else $loaded_number = 0;
            $notifications = getConnectedUser()->getNotifications($loaded_number);
            setcookie("loadedNotificationsNumber", $loaded_number + 10, time() + 86400, "/" );
        }

        foreach ($notifications as $notification) $notification->getText();

        echo json_encode($notifications);
    }

    if (isset($_GET['check'])) {

        // In case check value is 'all', set all loaded notifications to checked
        if ($_GET['check'] == 'all') {
            if (isset($_COOKIE["loadedNotificationsNumber"])) $limit = $_COOKIE["loadedNotificationsNumber"];
            else $limit = 10;
            $notifications = getConnectedUser()->getNotifications(0, $limit);
            foreach ($notifications as $notification) $notification->check();
            echo json_encode(true);
            
        // In case check value is an id, set corresponding notification to checked
        } else {
            $notification = new Notification($_GET['check']);
            $notification->check();
            echo json_encode(true);
        }
    }
}