import NotificationsHandler from "/class/NotificationsHandler.js"

// Get notifications from server
ajaxGetRequest('/api/notifications.php?get=true&reset=true', (notifications) => {

    var notificationsHandler = new NotificationsHandler(notifications)

    // If at least one new notification, show icon
    if (notificationsHandler.getUncheckedNumber() > 0) notificationsHandler.showIcon()
} )