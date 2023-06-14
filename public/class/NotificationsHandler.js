export default class NotificationsHandler {

    constructor (data = null) {
        if (data) this.data = data
        this.container = document.getElementById('notificationsContainer')
    }

    data
    container
    icon
    windowDisplayed = false

    /**
     * Load newest notifications from server
     */
    loadNotifications () {
        return new Promise((resolve, reject) => {
            ajaxGetRequest('/api/notifications.php?get=true&reset=true', (notifications) => {
                this.data = notifications
                this.hideIcon()
                this.showIcon()
                resolve(notifications)
            } )
        } )
    }

    /**
     * Get number of new notifications
     */
    getUncheckedNumber () {
        var uncheckedNumber = 0
        this.data.forEach(notification => {
            if (notification.checked == false) uncheckedNumber++
        } )
        return uncheckedNumber
    }

    /**
     * Append interactive icon to {this.container}
     */
    showIcon () {

        // If more than one unchecked notification exists, display icon
        let uncheckedNumber = this.getUncheckedNumber()
        if (uncheckedNumber > 0) {

            // Display icon
            this.icon = document.createElement('div')
            this.icon.className = 'notifications-icon'
            this.icon.innerText = uncheckedNumber
            this.container.appendChild(this.icon)

            // Show notifications list on click
            this.icon.addEventListener('click', () => {
                this.showList()
            } )
        }
    }

    hideIcon () {
        this.icon.remove()
    }

    /**
     * Show a list of notifications details
     */
    showList () {

        // If {this.window} is not shown, display it
        if (!this.windowDisplayed) {

            // Build window container
            this.window = document.createElement('div')
            this.window.className = 'notifications-window'

            // Append each element
            this.data.forEach(notification => {
                let element = document.createElement('div')
                element.className = 'notifications-element'
                if (!notification.checked) element.classList.add('new')
                let time = new Date(notification.datetime.date)
                element.innerHTML = notification.text + '<span class="notifications-element-time">' + time.toLocaleString('ja-JP', {timeZone: 'Asia/Tokyo'}) + '</span>'
                this.window.appendChild(element)
                // Set click listener
                element.addEventListener('click', async () => {
                    await this.setToChecked(notification.id)
                    if (notification.ref) window.location.href = '/' + notification.ref
                } )
            } )
            // Append 'check all notifications' element
            let $checkAll = document.createElement('div')
            $checkAll.className = 'notifications-element notifications-element-checkall'
            $checkAll.innerText = '全て確認しました'
            $checkAll.addEventListener('click', async () => {
                $checkAll.style.cursor = 'progress'
                await this.setToChecked('all')
                await this.loadNotifications()
                this.window.remove()
                this.windowDisplayed = false
            } )
            this.window.appendChild($checkAll)

            this.container.after(this.window)

        // Else, remove it
        } else this.window.remove()

        // Toggle {this.windowDisplayed} property
        this.windowDisplayed = !(this.windowDisplayed)
    }

    async setToChecked (id) {
        return new Promise ((resolve, reject) => {
            ajaxGetRequest('/api/notifications.php?check=' + id, (response) => {
                resolve(response)
            } )
        } )
    }

}