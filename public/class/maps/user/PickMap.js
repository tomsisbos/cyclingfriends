import Map from "/class/maps/Map.js"

export default class PickMap extends Map {

    constructor () {
        super()
        this.getUserLocation().then((userLocation) => this.currentPosition = userLocation)
    }

    apiUrl = '/api/riders/location.php'
    currentPosition
    
    async centerOnUserLocation () {
        if (this.map) {
            if (this.currentPosition) this.map.setCenter(this.currentPosition)
            else this.map.setCenter(await this.getUserLocation())
        }
    }

}