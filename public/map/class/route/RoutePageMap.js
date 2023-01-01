import GlobalMap from "/map/class/GlobalMap.js"
import CheckpointPopup from "/map/class/CheckpointPopup.js"

export default class RoutePageMap extends GlobalMap {
    
    constructor () {
        super()
        if (location.pathname.includes('ride')) {
            this.rideId = getIdFromString(location.pathname)
        } else if (location.pathname.includes('route')) {
            this.routeId = getIdFromString(location.pathname)
        }
    }

    mkpointsOnRouteNumber = 0
    apiUrl = '/api/route.php'
    data
    mkpoints
    ride
    routeId
    rideId

    // Set another map style without interfering with user build route
    setMapStyle (layerId) {
        
        // Save layers
        var routeStyle = this.saveRouteStyle()

        // Clear route
        this.clearRoute()

        // Change map style
        this.map.setStyle('mapbox://styles/sisbos/' + layerId).once('idle', async () => {
            this.loadImages()
            this.addSources()
            this.addLayers()
            this.loadRouteStyle(routeStyle)
            this.updateDistanceMarkers()
            if (!this.rideId) this.displayStartGoalMarkers(this.map.getSource('route')._data)
        } )
    }

    addRouteLayer (geojson) {
        this.map.addLayer( {
            id: 'route-cap',
            type: 'line',
            source: {
                type: 'geojson',
                data: geojson
            },
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.routeCapColor,
                'line-width': 3,
                'line-opacity': 1,
                'line-gap-width': 3
            }
        } )
        this.map.addLayer( {
            id: 'route',
            type: 'line',
            source: {
                type: 'geojson',
                lineMetrics: true,
                data: geojson
            },
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.routeColor,
                'line-width': 5,
                'line-opacity': 1
            }
        } )
        this.prepareTooltip()
        // Prevent profile point of remaining on the map after leaving profile canvas area
        this.map.on('mousemove', () => {
            if (this.map.getLayer('profilePoint')) {
                this.map.removeLayer('profilePoint')
                this.map.removeSource('profilePoint')
            }
        } )
    }

    // Build route specs table from scratch
    buildTable () {

        // Build variable
        var tableData = []
        
        // Add each mkpoint
        for (let i = 0; i < this.mkpoints.length; i++) {
            if (this.mkpoints[i].on_route) var remoteness = 'コース上'
            else var remoteness = Math.floor(this.mkpoints[i].remoteness * 10) / 10 + 'km'
            let entry = {
                type: 'mkpoint',
                lngLat: {lng: this.mkpoints[i].lngLat.lng, lat: this.mkpoints[i].lngLat.lat},
                id: this.mkpoints[i].id,
                name: this.mkpoints[i].name,
                description: this.mkpoints[i].description,
                geolocation: this.mkpoints[i].city + ', ' + this.mkpoints[i].prefecture,
                distance: 'km ' + Math.floor(this.mkpoints[i].distance * 10) / 10,
                distanceValue: this.mkpoints[i].distance,
                elevation: this.mkpoints[i].elevation + 'm',
                remoteness
            }
            tableData.push(entry)
        }

        // Add each checkpoint
        if (this.rideId) {
            for (let i = 0; i < this.ride.checkpoints.length; i++) {
                if (this.ride.checkpoints[i].city) var geolocation = this.ride.checkpoints[i].city + ', ' + this.ride.checkpoints[i].prefecture
                else var geolocation = ''
                let entry = {
                    type: 'checkpoint',
                    lngLat: this.ride.checkpoints[i].lngLat,
                    id: this.ride.checkpoints[i].number,
                    name: this.ride.checkpoints[i].name,
                    description: this.ride.checkpoints[i].description,
                    geolocation,
                    distance: 'km ' + Math.floor(this.ride.checkpoints[i].distance * 10) / 10,
                    distanceValue: Math.floor(this.ride.checkpoints[i].distance * 100 ) / 100,
                    elevation: this.ride.checkpoints[i].elevation + 'm',
                    remoteness: 'コース上'
                }
                tableData.push(entry)
            }
        }

        // Sort table entries
        tableData.sort((a,b) => a.distanceValue - b.distanceValue)

        // Build table
        var tbody = document.querySelector('#routeTable tbody')
        var previousEntry
        tableData.forEach( (entry) => {

            var tr = document.createElement('tr')
            if (entry.remoteness != 'コース上') tr.classList.add('offroute')
            var td = []
            if (previousEntry && entry.distance == previousEntry.distance) var ignore = true // Ignore if similar entry
            tr.id = entry.type + entry.id
            // Create tds
            for (let i = 1; i <= 5; i++) {
                td[i] = document.createElement('td')
            }
            // Populate tds
            td[1].innerHTML = entry.distance
            td[2].innerHTML = entry.name
            td[3].innerHTML = entry.geolocation
            td[4].innerHTML = entry.elevation
            td[5].innerHTML = entry.remoteness
            // Style tds
            td[1].className = 'text-left'
            td[2].className = 'text-left'
            td[3].className = 'text-center'
            td[4].className = 'text-center'
            td[5].className = 'text-center'
            // Append tds
            for (let i = 1; i <= 5; i++) tr.appendChild(td[i])
            if (!ignore) tbody.appendChild(tr)
            previousEntry = entry

            // Set entry event listener
            tr.addEventListener('click', (e) => {
                var target = e.target.closest('tr')
                // If clicked thumbnail is not already selected
                if (!target.classList.contains('selected-entry')) {
                    // Toggle popup and add selected class to corresponding marker and table entry
                    if (this.map) this.map._markers.forEach( (marker) => {
                        var $marker = marker.getElement()
                        if (getIdFromString($marker.id) == entry.id || this.ride && (this.ride.options.sf == true && getIdFromString($marker.id) == 0 && entry.id == this.ride.checkpoints.length - 1)) {
                            marker.togglePopup()
                            $marker.classList.add('selected-marker')
                            document.querySelector('#routeTable #' + entry.type + entry.id).classList.add('selected-entry')
                            // Add selected-marker class
                            if (this.ride && ((this.ride.options.sf == false && entry.remoteness == 'コース上') || (this.ride.options.sf == true && entry.remoteness == 'コース上' && entry.id != this.ride.checkpoints.length - 1))) {
                                // To clicked marker
                                document.querySelector('.mapboxgl-canvas-container #' + entry.type + entry.id).classList.add('selected-marker')
                                // To clicked thumbnail
                                document.querySelector('.rt-slider #' + entry.type + entry.id).querySelector('img').classList.add('selected-marker')
                            } else if (entry.id == 0) { // If click on goal on a ride with same start and finish
                                // To clicked marker
                                document.querySelector('.mapboxgl-canvas-container #' + entry.type + 0).classList.add('selected-marker')
                                // To clicked thumbnail
                                document.querySelector('.rt-slider #' + entry.type + 0).querySelector('img').classList.add('selected-marker')
                            }
                        } else {
                            if (marker.getPopup().isOpen()) marker.getPopup().remove()
                            $marker.classList.remove('selected-marker')
                        }
                    } )
                    // Remove selected class from other thumbnails and table entries
                    document.querySelectorAll('.rt-preview-photo').forEach( (thumbnail) => {
                        if (thumbnail.id != entry.type + entry.id && (!this.ride || !(this.ride.options.sf == true && entry.id == this.ride.checkpoints.length - 1))) thumbnail.querySelector('img').classList.remove('selected-marker')
                    } )
                    document.querySelectorAll('#routeTable tr').forEach( (tableEntry) => {
                        if (tableEntry != target) tableEntry.classList.remove('selected-entry')
                    } )
                    if (document.querySelector('#boxShowMkpoints') && document.querySelector('#boxShowMkpoints').checked) {
                        // Fly to the marker location
                        console.log(entry)
                        this.map.flyTo( {
                            center: [entry.lngLat.lng, entry.lngLat.lat],
                            zoom: 14,
                            speed: 0.8,
                            curve: 1,
                            pitch: 40,
                            easing(t) {
                                return t
                            }
                        } )
                    }
                // If clicked entry is already selected
                } else {
                    // Unselect
                    target.classList.remove('selected-entry')
                    if ((this.ride.options.sf == true && entry.type == 'checkpoint' && entry.id == this.ride.checkpoints.length - 1)) { // Unselect start marker if click on goal
                        document.querySelector('.mapboxgl-canvas-container #' + entry.type + 0).classList.remove('selected-marker')
                    } else document.querySelector('.mapboxgl-canvas-container #' + entry.type + entry.id).classList.remove('selected-marker')
                    // Focus
                    this.focus(this.map.getSource('route')._data)
                    // Close corresponding popup
                    this.map._markers.forEach( (marker) => {
                        if (getIdFromString(marker.getElement().id) == entry.id || this.ride && (this.ride.options.sf == true && getIdFromString(marker.getElement().id) == 0 && entry.id == this.ride.checkpoints.length - 1)) {
                            marker.togglePopup()
                        }
                    } )
                }
                ///this.generateProfile()
            } )
        } )
    }

    buildSlider () {

        // Build variable
        var sliderData = []
                
        // Add each mkpoint
        for (let i = 0; i < this.mkpoints.length; i++) {
            if (this.mkpoints[i].on_route) {
                
                if (this.mkpoints[i].file_blob !== null) var thumbnailSrc = 'data:image/jpeg;base64,' + this.mkpoints[i].file_blob
                else var thumbnailSrc = '/media/default-photo-' + Math.ceil(Math.random() * 9) + '.svg'
                let entry = {
                    type: 'mkpoint',
                    lngLat: {lng: this.mkpoints[i].lngLat.lng, lat: this.mkpoints[i].lngLat.lat},
                    id: this.mkpoints[i].id,
                    name: this.mkpoints[i].name,
                    distance: 'km ' + Math.floor(this.mkpoints[i].distance * 10) / 10,
                    distanceValue: this.mkpoints[i].distance, 
                    thumbnailSrc: 'data:image/jpeg;base64,' + this.mkpoints[i].file_blob
                }
                sliderData.push(entry)
            }
        }

        // Add each checkpoint
        if (this.rideId) {
            for (let i = 0; i < this.ride.checkpoints.length; i++) {
                if (this.ride.checkpoints[i].img.blob !== null) var thumbnailSrc = 'data:image/jpeg;base64,' + this.ride.checkpoints[i].img.blob
                else var thumbnailSrc = '/media/default-photo-' + Math.ceil(Math.random() * 9) + '.svg'
                let entry = {
                    type: 'checkpoint',
                    lngLat: this.ride.checkpoints[i].lngLat,
                    id: this.ride.checkpoints[i].number,
                    name: this.ride.checkpoints[i].name,
                    distance: 'km ' + Math.floor(this.ride.checkpoints[i].distance * 10) / 10,
                    distanceValue: Math.floor(this.ride.checkpoints[i].distance * 100) / 100,
                    thumbnailSrc
                }
                sliderData.push(entry)
            }
        }

        // Sort table entries
        sliderData.sort((a,b) => a.distanceValue - b.distanceValue)

        // Populate slider
        var cursor = 0
        var previousEntry
        sliderData.forEach( (entry) => {

            cursor++

            // Ignore if similar entry
            if (!(previousEntry && entry.distance == previousEntry.distance)) {

                // Build thumbnail element
                var thumbnail = document.createElement('div')
                thumbnail.className = 'rt-preview-photo'
                thumbnail.id = entry.type + entry.id
                document.querySelector('.rt-slider').appendChild(thumbnail)
                var img = document.createElement('img')
                img.src = entry.thumbnailSrc
                thumbnail.appendChild(img)
                var distance = document.createElement('div')
                distance.innerText = entry.distance
                distance.className = 'rt-preview-photos-distance'
                thumbnail.appendChild(distance)
                if (cursor < sliderData.length) {
                    var svg = document.createElement('svg')
                    svg.innerHTML = `
                        <svg height="80" width="10">
                            <polygon points="0,00 10,40 0,80" />
                        </svg>`
                    thumbnail.after(svg)
                }

                // Set thumbnail event listener
                thumbnail.addEventListener('click', (e) => {
                    var target = e.target.closest('.rt-preview-photo')

                    // If clicked thumbnail is not already selected
                    if (!e.target.classList.contains('selected-marker')) {
                        // Toggle popup and add selected-marker class to corresponding marker and table entry
                        this.map._markers.forEach( (marker) => {
                            var $marker = marker.getElement()
                            if (getIdFromString($marker.id) == entry.id || this.ride && (this.ride.options.sf == true && getIdFromString($marker.id) == 0 && entry.id == this.ride.checkpoints.length - 1)) { // Added code to select start when SF is selected as goal
                                marker.togglePopup()
                                $marker.classList.add('selected-marker')
                                document.querySelector('#routeTable #' + entry.type + entry.id).classList.add('selected-entry')
                                // Add selected-marker class to clicked thumbnail
                                target.querySelector('img').classList.add('selected-marker')
                            } else {
                                if (marker.getPopup().isOpen()) marker.getPopup().remove()
                                $marker.classList.remove('selected-marker')
                            }
                        } )
                        // Remove selected-marker class from other thumbnails and table entries
                        document.querySelectorAll('.rt-preview-photo').forEach( (thumbnail) => {
                            if (thumbnail != target) thumbnail.querySelector('img').classList.remove('selected-marker') 
                        } )
                        document.querySelectorAll('#routeTable tr').forEach( (tr) => {
                            if (tr.id != entry.type + entry.id) tr.classList.remove('selected-entry')
                        } )
                        // Fly to the marker location
                        console.log(entry)
                        this.map.flyTo( {
                            center: entry.lngLat,
                            zoom: 14,
                            speed: 0.8,
                            curve: 1,
                            pitch: 40,
                            easing(t) {
                                return t
                            }
                        } )

                    // If clicked thumbnail is already selected
                    } else {
                        // Unselect
                        e.target.classList.remove('selected-marker')
                        document.querySelector('#routeTable #' + entry.type + entry.id).classList.remove('selected-entry')
                        // Focus
                        this.focus(this.map.getSource('route')._data)
                        // Close corresponding popup
                        this.map._markers.forEach( (marker) => {
                            if (marker.getElement().classList.contains('selected-marker')) marker.getElement().classList.remove('selected-marker')
                            if (getIdFromString(marker.getElement().id) == entry.id || this.ride && (this.ride.options.sf == true && getIdFromString(marker.getElement().id) == 0 && entry.id == this.ride.checkpoints.length - 1)) marker.togglePopup()
                        } )
                    }
                } )
                previousEntry = entry
            }
        } )
    }

    loadRide () {
        return new Promise ( async (resolve, reject) => {

            ajaxGetRequest (this.apiUrl + "?ride-load=" + this.rideId, async (ride) => {

                // Store ride properties inside map instance
                if (Math.round(ride.checkpoints[0].lngLat.lng * 1000) / 1000 == Math.round(ride.checkpoints[ride.checkpoints.length - 1].lngLat.lng * 1000) / 1000 && Math.round(ride.checkpoints[0].lngLat.lat * 1000) / 1000 == Math.round(ride.checkpoints[ride.checkpoints.length - 1].lngLat.lat * 1000) / 1000) {
                    ride.options = {sf: true}
                    ride.checkpoints[ride.checkpoints.length - 1].distance = Math.floor(turf.length(this.data.routeData) * 10) / 10
                }
                else ride.options = {sf: false}
                this.ride = ride

                await this.generateCheckpointPoi()

                resolve()

                // Display ride checkpoints on the course
                this.displayCheckpoints(ride)
                this.generateProfile()
            } )
        } )
    }

    displayCheckpoints () {
        this.ride.checkpoints.forEach( (checkpoint) => {
            console.log(checkpoint)
            if (this.ride.options.sf != true || checkpoint.number != this.ride.checkpoints.length - 1) this.addMarker(checkpoint)
            // Remove mkpoints in double
            this.mkpoints.forEach( (mkpoint) => {
                if (Math.ceil(checkpoint.distance * 100) / 100 == Math.ceil(mkpoint.distance * 100) / 100) {
                    console.log(checkpoint.name + ' and ' + mkpoint.name + ' are in double')
                    document.querySelector('#mkpoint' + mkpoint.id).style.display = 'none'
                }
            } )
        } )
    }

    generateCheckpointPoi () {
        return new Promise( (resolve, reject) => {
            this.ride.checkpoints.forEach( (checkpoint) => {
                let canvas = document.createElement('canvas')
                canvas.height = 50
                canvas.width = 50
                let ctx = canvas.getContext("2d")
                ctx.font = "bold 35px Noto Sans"
                if (checkpoint.number == 0) {
                    ctx.fillStyle = 'green'
                    var text = 'S'
                } else if (checkpoint.number == this.ride.checkpoints.length - 1) {
                    ctx.fillStyle = 'red'
                    var text = 'F'
                } else {
                    ctx.fillStyle = 'blue'
                    var text = checkpoint.number
                }
                ctx.rect(0, 0, 50, 50)
                ctx.fill()
                ctx.fillStyle = 'white'
                ctx.fillText(text, 15, 40)
                var img = new Image()
                ctx.drawImage (img, 0, 0)
                img.src = canvas.toDataURL()
                img.classList.add('js-poi-icon')
                img.id = 'checkpointPoiIcon' + checkpoint.number
                img.style.display = 'none'
                document.querySelector('#elevationProfile').appendChild(img)
            } )
            resolve(true)
        } )
    }

    addMarker (checkpoint) {
        var element = this.createCheckpointElement(checkpoint.number)
        var marker = new mapboxgl.Marker(
            {
                draggable: false,
                scale: 0.8,
                element: element
            }
        )
        marker.setLngLat(checkpoint.lngLat)
        marker.addTo(this.map)

        // Generate popup
        let checkpointPopup = new CheckpointPopup(checkpoint)
        let popup = checkpointPopup.popup
        popup.on('open', () => {
            this.unselectMarkers()
            checkpointPopup.select()
            this.generateProfile()
            checkpointPopup.setTarget() // Set target button
        } )
        popup.on('close', () => {
            this.unselectMarkers()
        } )
        marker.setPopup(popup)

        // Set modal
        checkpointPopup.setModal()

        // Set cursor pointer on mouse hover
        marker.getElement().style.cursor = 'pointer'

        return marker
    }

    createCheckpointElement (number) {
        var element = document.createElement('div')
        element.className = 'checkpoint-marker'
        element.id = 'checkpoint' + number
        if (number == 0 && this.ride.options.sf == false) { // If this is the first marker, set it to 'S'
            element.innerHTML = 'S'
            element.className = 'checkpoint-marker checkpoint-marker-start'
        } else if (number == 0 && this.ride.options.sf == true) {
            element.innerHTML = 'SF'
            element.className = 'checkpoint-marker checkpoint-marker-startfinish'
        } else if (this.ride.options.sf == false && number == this.ride.checkpoints.length - 1) { // If this is the last marker, set it to 'F'
            element.innerHTML = 'F'
            element.className = 'checkpoint-marker checkpoint-marker-goal'
        } else { // Else, set it to number
            element.innerHTML = number
        }
        return element
    }

}