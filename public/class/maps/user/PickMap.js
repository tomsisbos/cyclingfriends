import Map from "/class/maps/Map.js"

// Global class initialization
export default class PickMap extends Map {

    constructor () {
        super()
    }

    apiUrl = '/api/riders/location.php'
    
    centerOnUserLocation () {
        if (this.map) {
            if (this.currentPosition) this.map.setCenter(this.currentPosition)
            else this.map.setCenter(this.userLocation)
        }
    }

}