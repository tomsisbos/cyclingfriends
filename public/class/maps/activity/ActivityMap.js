import Map from "/class/maps/Map.js"
import Popup from "/class/maps/Popup.js"
import CFUtils from "/class/utils/CFUtils.js"
import CFSession from "/class/utils/CFSession.js"

export default class ActivityMap extends Map {

    constructor () {
        super()
        this.activityId = parseInt(document.querySelector('#activityMap').dataset.id)
    }

    pageType = 'activity'
    apiUrl = '/api/activity.php'
    data
    cursor = 0
    activityId
    icons = {
        Start: 'material-symbols/play-circle',
        Landscape: 'bxs/landscape',
        Break: 'ic/round-pause-circle',
        Restaurant: 'ion/restaurant',
        Cafe: 'medical-icon/i-coffee-shop',
        Attraction: 'gis/layer-poi',
        Event: 'entypo/info-with-circle',
        Goal: 'material-symbols/stop-circle'
    }

    calculateElevation (trackpoints) {
        var elevation = 0
        for (let i = 1; i < trackpoints.length - 1; i++) {
            if (trackpoints[i].elevation > trackpoints[i - 1].elevation) {
                elevation += (trackpoints[i].elevation - trackpoints[i - 1].elevation)
            }
        }
        return Math.ceil(elevation * 10) / 10
    }

    async addMarkerOnRoute (lngLat, type = false) {
        return new Promise(async (resolve, reject) => {
            // Generate new marker
            var marker = this.addMarker(lngLat, type)
            await this.sortCheckpoints()
            this.updateMarkers()
            this.updateCheckpointForms()
            resolve(marker)
        } )
    }

    // Generate new marker
    addMarker (lngLat, type = false) {
        var number = this.setCheckpointNumber(this.data.checkpoints, lngLat)
        var element = this.createCheckpointElement(number, type)
        var marker = new mapboxgl.Marker(
            {
                draggable: false,
                scale: 0.8,
                element
            }
        )
        marker.setLngLat(lngLat)
        marker.addTo(this.map)

        // Get corresponding datetime and temperature

        // If adding a new checkpoint
        if (!type) {

            // If working with an extacted log file
            if (this.activityData) {
                var correspondingTrackpoint
                var bestCloseness = 9999
                for (let i = 0; i < this.activityData.linestring.trackpoints.length; i++) {
                    let closeness = turf.distance(turf.point([lngLat.lng, lngLat.lat]), turf.point([this.activityData.linestring.coordinates[i].lng, this.activityData.linestring.coordinates[i].lat]))
                    if (closeness < bestCloseness) {
                        bestCloseness = closeness
                        correspondingTrackpoint = this.activityData.linestring.trackpoints[i]
                    }
                }
                var datetime = correspondingTrackpoint.time
                var temperature = parseInt(correspondingTrackpoint.temperature)
            // If working with a previously saved cyclingfriends activity data
            } else {
                var correspondingPoint = CFUtils.replaceOnRoute([lngLat.lng, lngLat.lat], this.routeData)
                var index = CFUtils.getCoordIndex(correspondingPoint, this.routeData.geometry.coordinates)
                var datetime = this.routeData.properties.time[index]
                var temperature = 0 /// Temperature data not saved in coords table data
            }

            // Update data
            this.data.checkpoints[this.cursor] = {
                name: '',
                type: 'Landscape',
                story: '',
                lngLat: marker.getLngLat(),
                datetime,
                elevation: Math.floor(this.map.queryTerrainElevation(marker.getLngLat())),
                temperature,
                marker
            }

            // Add remove listener on click (except for start and goal markers)
            const routeCoordinates = this.routeData.geometry.coordinates
            if (lngLat != routeCoordinates[0] && lngLat != routeCoordinates[routeCoordinates.length - 1]) {
                marker.getElement().addEventListener('contextmenu', this.removeOnClickHandler)
            }

        // If displaying default number markers
        } else if (type == 'default') {

            var checkpoint = this.data.checkpoints[number]
            var content = this.setCheckpointPopupContent(checkpoint)
            let checkpointPopup = new Popup({className: 'pg-ac-checkpoint-popup'})
            let popup = checkpointPopup.popup
            popup.setHTML(content)
            marker.setPopup(popup)

            // Add remove listener on click (except for start and goal markers)
            const routeCoordinates = this.routeData.geometry.coordinates
            if (lngLat != routeCoordinates[0] && lngLat != routeCoordinates[routeCoordinates.length - 1]) {
                marker.getElement().addEventListener('contextmenu', this.removeOnClickHandler)
            }

        // If displaying default type markers
        } else {

            var checkpoint = this.data.checkpoints[this.cursor]
            var content = this.setCheckpointPopupContent(checkpoint)
            let checkpointPopup = new Popup({className: 'pg-ac-checkpoint-popup'})
            let popup = checkpointPopup.popup
            popup.setHTML(content)
            marker.setPopup(popup)

        }
        
        // Set cursor pointer on mouse hover
        marker.getElement().style.cursor = 'pointer'
        
        this.cursor++

        return marker
    }

    createCheckpointElement (i, type = false) {
        if (type == false || type == 'default') {
            var element = document.createElement('div')
            element.className = 'checkpoint-marker'
            element.id = i
            element.innerHTML = i
        } else {
            var element = document.createElement('div')
            element.className = 'checkpoint-marker logo-checkpoint-marker'
            element.id = i
            var img = document.createElement('img')
            img.src = 'https://api.iconify.design/' + this.icons[type] + '.svg'
            element.appendChild(img)
        }
        return element
    }

    setCheckpointPopupContent (checkpoint) {
        var checkpointTime = checkpoint.datetime / 1000
        var startTime = this.data.checkpoints[0].datetime / 1000
        return `
            <div class="pg-ac-checkpoint-topline">
                <div>km ` + checkpoint.distance + `</div>
                <div class="pg-ac-checkpoint-time">(` + getFormattedDurationFromTimestamp(checkpointTime - startTime) + `)</div> - 
                <div>` + checkpoint.name + `</div>
            </div>
            <div class="pg-ac-checkpoint-story">
                ` + checkpoint.story + `
            </div>
        `
    }

    /**
     * Clear all photo markers of the map
     */
    clearPhotoMarkers () {
        this.map._markers.forEach(marker => {
            if (marker._element.classList.contains('pg-ac-map-img-container')) marker.remove()
        })
    }

    addPhoto (photo, lngLat) {
        var element = document.createElement('div')
        element.classList.add('pg-ac-map-img-container')
        element.dataset.id = photo.id
        element.innerHTML = '<img class="pg-ac-map-img" src="' + photo.url + '" />'
        var marker = new mapboxgl.Marker(
            {
                draggable: false,
                scale: 0.8,
                element
            }
        )
        marker.setLngLat(lngLat)
        marker.addTo(this.map)
        // Define growing method
        marker.grow = () => {
            setStyle(`
            .pg-ac-map-img-container.grown {
                max-height: ` + (this.$map.offsetHeight - 80) + `px !important;
                max-width: ` + (this.$map.offsetWidth - 80) + `px !important;
                top: ` + (18 - (this.$map.offsetHeight / 2)) + `px !important;
            }`)
            var isGrown = false
            if (marker.getElement().classList.contains('grown')) isGrown = true
            this.unselectPhotos()
            if (!isGrown) {
                marker.getElement().classList.add('grown')
                document.querySelectorAll('.pg-ac-photo').forEach( (element) => {
                    if (element.dataset.id == photo.id) element.classList.add('selected-marker')
                } )
            }

            function setStyle (cssText) {
                var sheet = document.createElement('style')
                document.head.appendChild(sheet)
                return (setStyle = (cssText, node) => {
                    if (!node || node.parentNode !== sheet) {
                        return sheet.appendChild(document.createTextNode(cssText))
                    }
                    node.nodeValue = cssText
                    return node
                }) (cssText)
            }
        }
        return marker
    }

    unselectPhotos () {
        this.map._markers.forEach( (marker) => {
            marker.getElement().classList.remove('grown')
        } )
        document.querySelectorAll('.pg-ac-photo').forEach( (element) => {
            element.classList.remove('selected-marker')
        } )
    }

    getPhotoLocation (photo, routeData = this.routeData) {
        const routeCoordinates = routeData.geometry.coordinates
        const routeTime = routeData.properties.time
        var smallestGap = routeTime[0]
        var closestCoordinate
        // Get closest route coordinate by looping through them
        for (let i = 0; i < routeCoordinates.length; i++) {
            const coordTime = routeTime[i]
            if (Math.abs(coordTime - photo.datetime) < smallestGap) {
                smallestGap = Math.abs(coordTime - photo.datetime)
                closestCoordinate = routeCoordinates[i]
            }
        }
        return closestCoordinate
    }

    getPhotoDistance (photo, routeData = this.routeData) {
        const photoLocation = this.getPhotoLocation(photo, routeData)
        var segment = turf.lineSlice(turf.point(routeData.geometry.coordinates[0]), turf.point(photoLocation), routeData)
        return turf.length(segment)
    }

    async displayPhotoMarkers () {
        return new Promise(async (resolve, reject) => {
            var sessionId = parseInt(await CFSession.get('id'))

            // Clear all previously displayed photo markers
            this.clearPhotoMarkers()

            // Add all photos to the map
            this.data.photos.forEach( (photo) => {
                // Only add photos which privacy is not set to true, except for the author
                if (photo.privacy != 'private' || sessionId == this.data.user_id) {
                    var lngLat = this.getPhotoLocation(photo)
                    photo.marker = this.addPhoto(photo, lngLat)
                }
            } )
            resolve(true)
        })
    }

    displayCheckpointMarkers () {
        this.data.checkpoints.forEach( (checkpoint) => {
            checkpoint.marker = this.addMarker(checkpoint.lngLat, checkpoint.type)
        } )
    }
}