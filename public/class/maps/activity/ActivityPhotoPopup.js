
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
        return `
        <div class="popup-img-container">
            <img class="popup-activity-photo" src="` + activityPhoto.url + `">
        </div>`
    }

    init () {
        this.popup.once('open', async () => {

            console.log(this)

            this.loadLightbox()

        } )
    }

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
     * Get the marker element corresponding to popup instance data scenery id
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
        $map.querySelector('#activityPhoto' + this.data.activityPhoto.id).querySelector('.activity-photo-icon').classList.add('selected-marker')
    }

}