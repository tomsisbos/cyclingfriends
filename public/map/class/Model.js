import Env from "/map/Env.js"
import CFUtils from "/map/class/CFUtils.js"

export default class Model {

    constructor (options) {
        if ((!options || !options.noSession) && !this.session) ajaxGetRequest (this.mainApiUrl + "?get-session=true", (session) => {
            this.session = session
            if (session.lngLat && session.lngLat.lng !== 0) this.userLocation = session.lngLat
            else this.userLocation = this.defaultCenter
            if (this.centerOnUserLocation) this.centerOnUserLocation()
        } )
    }

    apiKey = Env.mapboxApiKey
    mainApiUrl = '/api/map.php'

    defaultStyle = 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z'
    tags = ['hanami', 'kouyou', 'ajisai', 'culture', 'machinami', 'shrines', 'teafields', 'ricefields', 'sea', 'mountains', 'forest', 'rivers', 'lakes']
    userLocation
    loaderContainer = document.body
    loader = {
        prepare: () => {
            this.loaderElement = document.createElement('div')
            this.loaderElement.className = 'loader-element'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerHTML = '<div class="loader-center"></div>'
            loaderIcon.className = 'loader-icon'
            this.loaderElement.appendChild(loaderIcon)
        },
        start: () => this.loaderContainer.appendChild(this.loaderElement),
        stop: () => this.loaderElement.remove()
    }
    inlineLoader = '<div class="loader-inline"></div>'
    centerLoader = '<div class="loader-center"></div>'
    centerOnUserLocation = () => {return}

    // Get location of a LngLat point
    async getLocation (lngLat) {
        return new Promise ((resolve, reject) => {
            var lng = lngLat.lng
            var lat = lngLat.lat
            ajaxGetRequest ('https://api.mapbox.com/search/v1/reverse/' + lng + ',' + lat + '?language=ja&access_token=' + this.apiKey, callback)
            function callback (response) {
                console.log('MAPBOX GEOCODING API USE +1')
                var geolocation = CFUtils.reverseGeocoding (response)
                resolve (geolocation)
            }
        } )
    }
}