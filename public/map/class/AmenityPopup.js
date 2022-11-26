import Popup from "/map/class/Popup.js"

export default class AmenityPopup extends Popup {

    constructor (properties) {
        super( {
            
            className: 'amenity-popup',
            closeOnClick: false
        } )
        this.data = properties
        this.load()
    }
    apiUrl = '/api/map.php'
    type = 'amenity'
    data

    load () {

        // Set content
        this.popup.setHTML(`
        <div>
            Here            
        </div>`)
    }
}