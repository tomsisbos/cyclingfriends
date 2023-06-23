
import Popup from "/class/maps/Popup.js"
import ActivityPhotoLightbox from "/class/maps/activity/ActivityPhotoLightbox.js"

export default class ActivityPhotoPopup extends Popup {

    constructor (options, data, instanceOptions) {
        super(options, {}, instanceOptions)
        this.data = data
        
        // Set popup element
        var content = this.setContent(data.activityPhoto)
        this.popup.setHTML(content)

        // Init interactions
        this.init()
    }
    
    apiUrl = '/api/map.php'
    type = 'activityPhoto'
    data
    photos
    lightbox

    setContent (activityPhoto) {

        if (activityPhoto.activity_privacy == 'public') var activity_string = `<a href="/activity/` + activityPhoto.activity_id + `" target="_blank">` + activityPhoto.activity_title + `</a>`
        else var activity_string = 'このアクティビティは公開されていません。'

        return `
        <div class="popup-img-container">
            <div class="popup-icons">
                <div id="target-button" title="この写真の撮影位置に移動する。">
                    <span class="iconify" data-icon="icomoon-free:target" data-width="20" data-height="20"></span>
                </div>
            </div>
            <img class="popup-activity-photo" src="` + activityPhoto.url + `">
            <div class="photo-period ` + setPeriodClass((new Date(activityPhoto.datetime.date)).getMonth() + 1) + `" style="display: inline-block;">` + activityPhoto.period + `</div>
        </div>
        
        <div id="popup-content" class="popup-content">
            <div class="d-flex gap">
                <div class="round-propic-container">
                    <a href="/rider/` + activityPhoto.user_id + `" target="_blank">
                        <img class="round-propic-img" />
                    </a>
                </div>
                <div class="popup-properties">
                    <div class="popup-properties-reference">
                        <div class="popup-properties-name">
                            @<a href="/rider/` + activityPhoto.user_id + `" target="_blank">`
                            + activityPhoto.user_login + 
                            `</a>
                        </div>
                        <div class="popup-properties-name">`
                            + activity_string + 
                        `</div>
                        <div class="popup-properties-name">`
                            + (new Date(activityPhoto.datetime.date)).toLocaleDateString() + 
                        `</div>
                    </div>
                </div>
            </div>
        </div>`
    }

    init () {
        this.popup.once('open', async () => {

            this.select()
            this.loadLightbox()

            // Define actions to perform on each popup display
            this.popup.on('open', () => {
                this.data.mapInstance.unselectMarkers()
                this.select()
            } )
            this.popup.on('close', () => this.data.mapInstance.unselectMarkers())

            // Setup interactions depending on content
            const content = this.popup._content.innerHTML
            if (content.includes('target-button')) this.setTarget()
            if (content.includes('round-propic-img')) this.addPropic(this.data.activityPhoto.user_id)
        } )
    }

    /*
    async populate () {
        return new Promise(async (resolve, reject) => {

            // Get scenery details
            if (!this.data.scenery.photos) {
                var scenery = await this.getDetails(this.data.scenery.id)
                this.data.scenery = { ...scenery }
            }

            // Build visited icon if necessary
            if (this.data.scenery.isCleared) {
                var visitedIcon = document.createElement('div')
                visitedIcon.id = 'visited-icon'
                visitedIcon.title = 'この絶景スポットを訪れたことがあります。'
                visitedIcon.innerHTML = `
                    <a href="/activity/` + scenery.isCleared + `" target="_blank">
                        <span class="iconify" data-icon="akar-icons:circle-check-fill" data-width="20" data-height="20"></span>
                    </a>
                `
            }

            // Build tagslist
            var tags = ''
            if (this.data.scenery.tags) this.data.scenery.tags.map((tag) => {
                tags += `
                <a target="_blank" href="/tag/` + tag + `">
                    <div class="popup-tag tag-dark">#` + CFUtils.getTagString(tag) + `</div>
                </a>`
            } )

            // Add administration panel if connected user has admin rights
            var sessionId = await CFSession.get('id')
            if (scenery.user_id == sessionId) {
                var adminPanel = document.createElement('div')
                adminPanel.id = 'sceneryAdminPanel'
                adminPanel.className = 'popup-content container-admin'
                adminPanel.innerHTML = `
                    <div class="popup-head">管理者ツール</div>
                    <div class="popup-buttons">
                        <button class="mp-button bg-button text-white" id="sceneryEdit">情報編集</button>
                        <button class="mp-button bg-button text-white" id="sceneryMove">位置変更</button>
                        <button class="mp-button bg-danger text-white" id="sceneryDelete">削除</button>
                    </div>
                `
                // Set markerpoint to draggable depending on if user is marker admin and has set edit mode to true or not
                if (this.popup && this.popup._map) var marker = this.getMarker()
                else resolve(false)
                if (marker && this.data.mapInstance.mode == 'edit') marker.setDraggable(true)
                else if (marker && this.data.mapInstance.mode == 'default') marker.setDraggable(false)
                this.popup._content.querySelector('#popup-content').before(adminPanel)
            }

            if (this.data.scenery.isFavorite) this.popup._content.querySelector('.js-favorite-button').classList.add('favoured')
            if (this.data.scenery.isCleared) this.popup._content.querySelector('.popup-icons').appendChild(visitedIcon)
            this.popup._content.querySelector('.popup-properties-location').innerHTML = this.data.scenery.city + ' (' + this.data.scenery.prefecture + ') - ' + this.data.scenery.elevation + 'm'
            this.popup._content.querySelector('.popup-description').innerHTML = this.data.scenery.description
            this.popup._content.querySelector('.js-tags').innerHTML = tags

            resolve(true)
        } )
    }*/

    // Setup lightbox
    loadLightbox (container = this.data.mapInstance.$map) {
        var lightboxData = {
            container,
            popup: this.popup,
            mapInstance: this.data.mapInstance,
            activityPhoto: this.data.activityPhoto
        }
        this.lightbox = new ActivityPhotoLightbox(lightboxData)
    }

    /**
     * Get the marker element corresponding to popup instance data activity photo id
     * @returns {mapboxgl.Marker}
     */
    getMarker () {
        var marker
        this.popup._map._markers.forEach( (_marker) => { // Get current marker instance
            if (_marker.getElement().id == 'activityPhoto' + this.data.activityPhoto.id) marker = _marker
        } )
        return marker
    }

    select () {
        var $map = this.data.mapInstance.$map
        $map.querySelector('#activityPhoto' + this.data.activityPhoto.id + ' > *').classList.add('selected-marker')
    }

}