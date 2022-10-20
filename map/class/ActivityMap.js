import GlobalMap from "/map/class/GlobalMap.js"
import Popup from "/map/class/Popup.js"
import CFUtils from "/map/class/CFUtils.js"

export default class ActivityMap extends GlobalMap {

    constructor () {
        super()
        this.activityId = getParam('id')
    }

    pageType = 'activity'
    apiUrl = '/actions/activities/activityApi.php'
    data
    cursor = 0
    activityId
    icons = {
        Start: 'material-symbols/not-started-rounded',
        Landscape: 'bxs/landscape',
        Break: 'ic/round-pause-circle',
        Restaurant: 'ion/restaurant',
        Cafe: 'medical-icon/i-coffee-shop',
        Attraction: 'gis/layer-poi',
        Event: 'entypo/info-with-circle',
        Goal: 'gis/finish'
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

    addMarkerOnRoute (lngLat, type = false) {
        // Generate new marker
        var marker = this.addMarker(lngLat, type)
        this.sortCheckpoints(this.data.routeData)
        this.updateMarkers()
        this.updateCheckpointForms()
        return marker
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
            if (this.data.trackpoints) {
                var correspondingTrackpoint
                var bestCloseness = 9999
                this.data.trackpoints.forEach(trackpoint => {
                    let closeness = turf.distance(turf.point([lngLat.lng, lngLat.lat]), turf.point([trackpoint.lngLat.lng, trackpoint.lngLat.lat]))
                    if (closeness < bestCloseness) {
                        bestCloseness = closeness
                        correspondingTrackpoint = trackpoint
                    }
                } )
                var datetime = correspondingTrackpoint.time.getTime()
                var temperature = parseInt(correspondingTrackpoint.temperature)
            // If working with a previously saved cyclingfriends activity data
            } else {
                var correspondingPoint = CFUtils.replaceOnRoute([lngLat.lng, lngLat.lat], this.data.routeData)
                var index = CFUtils.getCoordIndex(correspondingPoint, this.data.routeData.geometry.coordinates)
                console.log(index)
                var datetime = this.data.routeData.properties.time[index]
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
            const routeCoordinates = this.data.routeData.geometry.coordinates
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
            const routeCoordinates = this.data.routeData.geometry.coordinates
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
        var checkpointTime = new Date(checkpoint.datetime.date).getTime()
        var startTime = new Date(this.data.checkpoints[0].datetime.date).getTime()
        return `
            <div class="pg-ac-checkpoint-topline">
                km ` + checkpoint.distance + `
                <div class="pg-ac-checkpoint-time">
                     (` + getFormattedDurationFromTimestamp(checkpointTime - startTime) + `) 
                </div>` +
                checkpoint.name + `
            </div>
            <div class="pg-ac-checkpoint-story">
                ` + checkpoint.story + `
            </div>
        `
    }

    addPhoto (photo, lngLat) {
        var element = document.createElement('div')
        element.classList.add('pg-ac-map-img-container')
        element.dataset.id = photo.id
        element.innerHTML = '<img class="pg-ac-map-img" src="data:' + photo.type + ';base64,' + photo.blob + '" />'
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
                top: ` + (25 - (this.$map.offsetHeight / 2)) + `px !important;
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

    getPhotoLocation (photo) {
        const routeCoordinates = this.data.routeData.geometry.coordinates
        const routeTime = this.data.routeData.properties.time
        var smallestGap = new Date(routeTime[0].date).getTime()
        var closestCoordinate
        // Get closest route coordinate by looping through them
        for (let i = 0; i < routeCoordinates.length; i++) {
            const coordTime = new Date(routeTime[i].date).getTime()
            const photoTime = new Date(photo.datetime.date).getTime()
            if (Math.abs(coordTime - photoTime) < smallestGap) {
                smallestGap = Math.abs(coordTime - photoTime)
                closestCoordinate = routeCoordinates[i]
            }
        }
        return closestCoordinate
    }

    getFormattedTimeFromLngLat (lngLat) {
        var routeClosestCoordinate = CFUtils.replaceOnRoute(lngLat, this.data.routeData)
        var index = this.data.routeData.geometry.coordinates.findIndex((element) => element == routeClosestCoordinate)
        var datetime = new Date(this.data.routeData.properties.time[index].date)
        var timestamp = datetime.getTime() - new Date(this.data.routeData.properties.time[0].date).getTime()
        return getFormattedDurationFromTimestamp(timestamp)
    }

    displayPhotos () {
        this.data.photos.forEach( (photo) => {
            var lngLat = this.getPhotoLocation(photo)
            photo.marker = this.addPhoto(photo, lngLat)
        } )
    }

    displayCheckpointMarkers () {
        this.data.checkpoints.forEach( (checkpoint) => {
            checkpoint.marker = this.addMarker(checkpoint.lngLat, checkpoint.type)
        } )
    }
}