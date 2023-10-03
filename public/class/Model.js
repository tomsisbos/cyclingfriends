import Env from "../map/Env.js"
import CFUtils from "../class/utils/CFUtils.js"

export default class Model {

    constructor () {
    }

    apiKey = Env.mapboxApiKey
    mainApiUrl = '/api/map.php'

    defaultStyle = 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z'
    tags = [
        'hanami-sakura',
        'hanami-ume',
        'hanami-nanohana',
        'hanami-ajisai',
        'hanami-himawari',

        'nature-forest',
        'nature-kouyou',
        'nature-ricefield',
        'nature-riceterraces',
        'nature-teafield',
        
        'water-sea',
        'water-river',
        'water-lake',
        'water-dam',
        'water-waterfall',

        'culture-culture',
        'culture-history',
        'culture-machinami',
        'culture-shrines',
        'culture-hamlet',

        'terrain-pass',
        'terrain-mountains',
        'terrain-viewpoint',
        'terrain-tunnel',
        'terrain-bridge'
    ]
    loaderContainer = document.body
    loader = {
        prepare: () => {
            this.element = document.createElement('div')
            this.element.className = 'loader-element'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerHTML = '<div class="loader-center"></div>'
            loaderIcon.className = 'loader-icon'
            this.element.appendChild(loaderIcon)
        },
        start: () => this.loaderContainer.appendChild(this.element),
        stop: () => this.element.remove()
    }
    inlineLoader = '<div class="loader-inline"></div>'
    centerLoader = '<div class="loader-center"></div>'
    centerOnUserLocation = () => {return}
    konbiniSearchNames = {
        'seven-eleven':  ["セブン", "sev", "7-E"],
        'family-mart': ["ファミリ",  "Fami", "Fimi", "サークル", "Circ"],
        'lawson': ["ローソン", "Laws", "LAWS"],
        'mini-stop': ["ミニスト", "Mini", "MINI"],
        'daily-yamazaki': ["Dail", "DAIL", "デイリー", "Yama", "ヤマザキ", "YAMA", "ニューヤ"]
    }

    // Get location of a LngLat point
    async getLocation (lngLat) {
        return new Promise ((resolve, reject) => {
            var lng = lngLat.lng
            var lat = lngLat.lat
            ajaxGetRequest ('https://api.mapbox.com/search/v1/reverse/' + lng + ',' + lat + '?language=ja&access_token=' + this.apiKey, callback)
            function callback (response) {
                var geolocation = CFUtils.reverseGeocoding (response)
                resolve (geolocation)
            }
        } )
    }    

    getRouteData (sourceName = 'route') {
        return new Promise((resolve, reject) => {
            if (this.data && this.routeData) {
                resolve(this.routeData)
            } else if (this.map.getSource(sourceName)) {
                resolve(this.map.getSource(sourceName)._data)
            } else {
                this.map.once('sourcedata', sourceName, (e) => {
                    if (e.isSourceLoaded == true) {
                        resolve(this.map.getSource(sourceName)._data)
                    }
                } )
            }
        } )
    }

    // Prepare tooltip display
    prepareTooltip () {
        this.map.on('mousemove', 'route', async (e) => {
            // Clear previous tooltip if displayed
            this.clearTooltip()
            // Prepare information to display
            this.map.getCanvas().classList.add('cursor-pointer')
            this.drawTooltip(this.map.getSource('route')._data, e.lngLat.lng, e.lngLat.lat, e.point.x, e.point.y)
        } )
        this.map.on('mouseout', 'route', () => {
            // Clear tooltip
            this.clearTooltip()
            this.map.getCanvas().classList.remove('cursor-pointer')
        } )
    }

    // Prepare data of [lng, lat] route point and draw tooltip at pointX/pointY position
    async drawTooltip (routeData, lng, lat, pointX, pointY = false, options) {
        var $profileBox = document.querySelector('#profileBox')
        var $elevationProfile = document.querySelector('#elevationProfile')
        
        // Distance and twin distance if there is one
        var result = CFUtils.findDistanceWithTwins(routeData, {lng, lat})
        var distance = result.distance
        var twinDistance = result.twinDistance

        // Altitude
        if (this.profile) var profile = this.profile
        else var profile = this
        if (profile.data == undefined) var profileData = await profile.getData(routeData)
        else var profileData = profile.data
        var altitude = profileData.averagedPointsElevation[Math.floor(distance * 10)]

        // Slope
        if (profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - profileData.averagedPointsElevation[Math.floor(distance * 10)]
        } else { // Only calculate on previous 100m for the last index (because no next index)
            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10)] - profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
        }
        /*
        if (!this.profileData) this.profileData = await this.profile.getData(routeData, {remote: false})
        var altitude = this.profileData.averagedPointsElevation[Math.floor(distance * 10)]

        // Slope
        if (this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
            var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - this.profileData.averagedPointsElevation[Math.floor(distance * 10)]
        } else { // Only calculate on previous 100m for the last index (because no next index)
            var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10)] - this.profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
        }*/

        // Build tooltip element
        var tooltip = document.createElement('div')
        tooltip.className = 'map-tooltip'
        if (twinDistance) {
            if (distance < twinDistance) {
                var dst1 = distance
                var dst2 = twinDistance
            } else {
                var dst1 = twinDistance
                var dst2 = distance
            }
            tooltip.innerHTML = `
            距離 : ` + dst1 + `km, ` + dst2 + `km<br>
            勾配 : <div class="map-slope">` + slope + `%</div><br>
            標高 : ` + altitude + `m`
        } else {
            tooltip.innerHTML = `
            距離 : ` + distance + `km<br>
            勾配 : <div class="map-slope">` + slope + `%</div><br>
            標高 : ` + altitude + `m`
        }
        // In case of an activity, add time data
        if (this.activityId) tooltip.innerHTML += '<br>時間 : ' + this.getFormattedTimeFromLngLat([lng, lat])
        
        // Position tooltip on the page
        // If height argument has been given, display on the map
        if (pointY) {
            this.$map.appendChild(tooltip)
            tooltip.style.left = pointX + 10 + 'px'
            tooltip.style.top = pointY + 10 + 'px'
            tooltip.style.borderRadius = '0px 10px 10px 10px'
        // Else, display on top of the profile by default
        } else {
            $profileBox.appendChild(tooltip)
            tooltip.style.left = pointX + 'px'
            tooltip.style.top = 0 - tooltip.offsetHeight + 'px'
            tooltip.style.borderRadius = '10px 10px 10px 0px'
            // Prevent tooltip from overflowing at the end of the profile
            if ((pointX + tooltip.offsetWidth) > $elevationProfile.offsetWidth - 10) {
                var corrector = (pointX + tooltip.offsetWidth) - ($elevationProfile.offsetWidth)
                tooltip.style.left = pointX - corrector + 'px'
            }
        }

        // Dynamic styling
        var slopeStyle = document.querySelector('.map-slope')
        slopeStyle.style.color = this.setSlopeStyle(slope).color
        slopeStyle.style.fontWeight = this.setSlopeStyle(slope).weight
        if (options) {
            if (options.backgroundColor) tooltip.style.backgroundColor = options.backgroundColor
            if (options.mergeWithCursor) tooltip.style.borderRadius = '4px 4px 4px 0px'
        }
    }

    clearTooltip () {
        var tooltip = document.querySelector('.map-tooltip')
        if (tooltip) tooltip.remove()
    }

    setSlopeStyle (slope) {
        if (slope <= -2) return {color: '#00e06e', weight: 'bold'}
        else if (slope > -2 && slope <= 2) return {color: '#000000', weight: 'normal'}
        else if (slope > 2 && slope <= 6) return {color: '#ffa500', weight: 'bold'}
        else if (slope > 6 && slope <= 9) return {color: '#ff5555', weight: 'bold'}
        else if (slope > 9) return {color: '#000000', weight: 'bold'}
    }

    getFormattedTimeFromLngLat (lngLat) {
        var routeClosestCoordinate = CFUtils.replaceOnRoute(lngLat, this.routeData)
        var index = this.routeData.geometry.coordinates.findIndex((element) => element == routeClosestCoordinate)
        var timestamp = this.routeData.properties.time[index] - this.routeData.properties.time[0]
        return getFormattedDurationFromTimestamp(timestamp)
    }

    /**
     * Remove 'selected-marker' class from all selected markers
     * @param {mapboxgl.Map} map
     */ 
    unselectMarkers (map) {
        var openedPopupId = 'none' // Prevent auto unselecting if popup opening and closing timing is wrong. If below condition is not included, style will not be switched in case of clicking another marker.
        // Remove selected class from map marker elements
        map._markers.forEach( (marker) => {
            if (!marker.getPopup() || !marker.getPopup().isOpen()) {
                marker.getElement().classList.remove('selected-marker')
                if (marker.getElement().querySelector('*')) marker.getElement().querySelector('*').classList.remove('selected-marker')
            } else openedPopupId = marker.getElement().id
        } )
        // Remove selected class from spec-table entries
        if (document.querySelector('.spec-table tr')) document.querySelectorAll('.spec-table tr').forEach( (tableEntry) => {
            if (tableEntry.id != '' && getIdFromString(openedPopupId) != getIdFromString(tableEntry.id)) {
                tableEntry.classList.remove('selected-entry')
            }
        } )
        // Remove selected class from slider thumbnails
        if (document.querySelector('.rt-slider img')) document.querySelectorAll('.rt-slider .rt-preview-photo').forEach( (thumbnail) => {
            if (getIdFromString(openedPopupId) != getIdFromString(thumbnail.id)) {
                thumbnail.querySelector('img').classList.remove('selected-marker')
            }
        } )
        // Remove selected class from POI icons
        if (document.querySelector('.js-poi-icon')) document.querySelectorAll('.js-poi-icon').forEach( (poiIcon) => {
            if (getIdFromString(openedPopupId) != getIdFromString(poiIcon.id)) {
                poiIcon.classList.remove('selected-marker')
            }
        } )
    }
}