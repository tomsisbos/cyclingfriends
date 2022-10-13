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
    apiUrl = '/map/api.php'
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