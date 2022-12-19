import CFUtils from "/map/class/CFUtils.js"

export default class Model {

    constructor () {
        ajaxGetRequest (this.apiUrl + "?get-session=true", (session) => {
            this.session = session
            sessionStorage.setItem('session-id', session.id)
            sessionStorage.setItem('session-login', session.login)
        } )
    }

    apiKey = 'pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdyNGYxbjAxd2MzbG12M3V1bjM1MGIifQ.bFRgCmK9_kkfZSd_skNF1g' // API Key (public mode for the moment)
    apiUrl = '/api/map.php'

    defaultStyle = 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z'
    tags = ['hanami', 'kouyou', 'ajisai', 'culture', 'machinami', 'shrines', 'teafields', 'sea', 'mountains', 'forest', 'rivers', 'lakes']
    loaderContainer = document.body
    loader = {
        prepare: () => {
            this.loaderElement = document.createElement('div')
            this.loaderElement.className = 'loader-element'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerText = 'Loading...'
            loaderIcon.className = 'loader-icon'
            this.loaderElement.appendChild(loaderIcon)
        },
        start: () => this.loaderContainer.appendChild(this.loaderElement),
        stop: () => this.loaderElement.remove()
    }

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