
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
                this.unselectMarkers(this.popup._map)
                this.select()
            } )
            const map = this.popup._map
            this.popup.on('close', () => this.unselectMarkers(map))

            // Setup interactions depending on content
            const content = this.popup._content.innerHTML
            if (content.includes('target-button')) this.setTarget()
            if (content.includes('round-propic-img')) this.addPropic(this.data.activityPhoto.user_id)

            // Set lightbox listener
            const photoElement = this.popup.getElement().querySelector('.popup-activity-photo')
            photoElement.addEventListener('click', () => {
                this.lightbox.open()
            } )
        } )
    }

    // Setup lightbox
    loadLightbox (container = this.popup._map.getContainer()) {
        var lightboxData = {
            container,
            popup: this.popup,
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
        var $map = this.popup._map.getContainer()
        $map.querySelector('#activityPhoto' + this.data.activityPhoto.id + ' > *').classList.add('selected-marker')
    }

}