import GlobalMap from "/map/class/GlobalMap.js"

// Global class initialization
export default class PickMap extends GlobalMap {

    constructor () {
        super()
    }

    apiUrl = '/api/riders/location.php'
    currentPosition = this.defaultCenter
    centerOnUserLocation = () => this.map.setCenter(this.currentPosition)

}