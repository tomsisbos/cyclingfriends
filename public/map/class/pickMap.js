import GlobalMap from "/map/class/GlobalMap.js"

// Global class initialization
export default class PickMap extends GlobalMap {

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