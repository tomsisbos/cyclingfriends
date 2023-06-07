import CFUtils from "/class/utils/CFUtils.js"
import Map from "/class/maps/Map.js"
import CheckpointPopup from "/class/maps/route/CheckpointPopup.js"
import CircleLoader from "/class/loaders/CircleLoader.js"

export default class RouteMap extends Map {
    
    constructor () {
        super()
        if (location.pathname.includes('ride')) {
            this.rideId = getIdFromString(location.pathname)
        } else if (location.pathname.includes('route')) {
            this.routeId = getIdFromString(location.pathname)
        }
    }

    sceneriesOnRouteNumber = 0
    apiUrl = '/api/route.php'
    data
    sceneries
    ride
    routeId
    rideId
    tableData = []
    tableEntries = []

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

    /**
     * Build route specs table
     * @param {Array} entriesToAdd list of entry types to add
     * @return {Promise}
     */ 
    async buildTable (entriesToAdd) {
        
        return new Promise(async (resolve, reject) => {

            var buildTableData = async () => {
                return new Promise((resolve, reject) => {
                    
                    // Add each scenery
                    if (entriesToAdd.includes('sceneries') && !this.tableEntries.includes('sceneries')) {
                        for (let i = 0; i < this.mapdata.sceneries.length; i++) {
                            if (this.mapdata.sceneries[i].on_route) var remoteness = 'コース上'
                            else var remoteness = Math.floor(this.mapdata.sceneries[i].remoteness * 10) / 10 + 'km'
                            let entry = {
                                type: 'scenery',
                                logo: '-',
                                lngLat: {lng: this.mapdata.sceneries[i].lng, lat: this.mapdata.sceneries[i].lat},
                                id: this.mapdata.sceneries[i].id,
                                name: this.mapdata.sceneries[i].name,
                                geolocation: this.mapdata.sceneries[i].city + ', ' + this.mapdata.sceneries[i].prefecture,
                                distance: 'km ' + Math.floor(this.mapdata.sceneries[i].distance * 10) / 10,
                                distanceValue: this.mapdata.sceneries[i].distance,
                                elevation: this.mapdata.sceneries[i].elevation + 'm',
                                remoteness
                            }
                            this.tableData.push(entry)
                        }
                        this.tableEntries.push('sceneries')
                    }

                    // Add each checkpoint
                    if (this.rideId) {
                        if (entriesToAdd.includes('checkpoints') && !this.tableEntries.includes('checkpoints')) {
                            for (let i = 0; i < this.ride.checkpoints.length; i++) {
                                if (this.ride.checkpoints[i].city) var geolocation = this.ride.checkpoints[i].city + ', ' + this.ride.checkpoints[i].prefecture
                                else var geolocation = ''
                                var logo = '-'
                                if (this.ride.checkpoints[i].special == 'meetingplace') logo = '<svg><circle fill="green" cx="10" cy="10" r="10"></circle><text x="42%" y="64%" text-anchor="middle" stroke="#fff">S</text></svg>'
                                else if (this.ride.checkpoints[i].special == 'finishplace') logo = '<svg><circle fill="red" cx="10" cy="10" r="10"></circle><text x="42%" y="64%" text-anchor="middle" stroke="#fff">F</text></svg>'
                                else logo = '<svg><circle fill="blue" cx="10" cy="10" r="10"></circle><text x="42%" y="64%" text-anchor="middle" stroke="#fff">' + this.ride.checkpoints[i].number + '</text></svg>'
                                let entry = {
                                    type: 'checkpoint',
                                    logo,
                                    lngLat: this.ride.checkpoints[i].lngLat,
                                    id: this.ride.checkpoints[i].number,
                                    name: this.ride.checkpoints[i].name,
                                    geolocation,
                                    distance: 'km ' + Math.floor(this.ride.checkpoints[i].distance * 10) / 10,
                                    distanceValue: Math.floor(this.ride.checkpoints[i].distance * 100 ) / 100,
                                    elevation: this.ride.checkpoints[i].elevation + 'm',
                                    remoteness: 'コース上'
                                }
                                this.tableData.push(entry)
                            }
                        }
                        this.tableEntries.push('checkpoints')
                    }
                    if (!entriesToAdd.includes('toilets') && !entriesToAdd.includes('water') && !entriesToAdd.includes('konbinis')) resolve(this.tableData)

                    var promises = 0
                    var promisesNumber = 0

                    // Add toilets
                    if (entriesToAdd.includes('toilets') && !this.tableEntries.includes('toilets')) {
                        promisesNumber++
                        fetch('/map/sources/compressed_sources/toilets.geojson')
                        .then(async (data) => {
                            var geojson = await data.json()
                            geojson.features.forEach(feature => {
                                if (typeof feature.geometry.coordinates[0] == 'object') var coordinates = feature.geometry.coordinates[0]
                                else var coordinates = feature.geometry.coordinates
                                var remotenessValue = turf.pointToLineDistance(coordinates, this.routeData)
                                if (remotenessValue < 0.1) var remoteness = 'コース上'
                                else var remoteness = Math.floor(remotenessValue * 1000) + 'm'
                                if (remotenessValue < 0.3) {
                                    let entry = {
                                        type: 'toilets',
                                        lngLat: {lng: coordinates[0], lat: coordinates[1]},
                                        id: feature.properties.id.replace('/', ''),
                                        logo: '<img src="/map/media/_icon-toilets.svg">',
                                        name: 'トイレ',
                                        geolocation: '-',
                                        distance: 'km ' + Math.floor(CFUtils.findDistanceWithTwins(this.routeData, {lng: coordinates[0], lat: coordinates[1]}).distance * 10) / 10,
                                        distanceValue: Math.floor(CFUtils.findDistanceWithTwins(this.routeData, {lng: coordinates[0], lat: coordinates[1]}).distance * 100) / 100,
                                        remoteness
                                    }
                                    if (this.map) entry.elevation = Math.floor(this.map.queryTerrainElevation(coordinates)) + 'm'
                                    else entry.elevation = '-'
                                    this.tableData.push(entry)
                                }
                            })
                            promises++
                            if (promises == promisesNumber) resolve(this.tableData)
                        })
                        this.tableEntries.push('toilets')
                    }

                    // Add water
                    if (entriesToAdd.includes('water') && !this.tableEntries.includes('water')) {
                        promisesNumber++
                        fetch('/map/sources/compressed_sources/drinking.geojson')
                        .then(async (data) => {
                            var geojson = await data.json()
                            geojson.features.forEach(feature => {
                                if (typeof feature.geometry.coordinates[0] == 'object') var coordinates = feature.geometry.coordinates[0]
                                else var coordinates = feature.geometry.coordinates
                                var remotenessValue = turf.pointToLineDistance(coordinates, this.routeData)
                                if (remotenessValue < 0.1) var remoteness = 'コース上'
                                else var remoteness = Math.floor(remotenessValue * 1000) + 'm'
                                if (remotenessValue < 0.3) {
                                    let entry = {
                                        type: 'drinking',
                                        lngLat: {lng: coordinates[0], lat: coordinates[1]},
                                        id: feature.properties.id.replace('/', ''),
                                        logo: '<img src="/map/media/_icon-water.svg">',
                                        name: '水分補給',
                                        geolocation: '-',
                                        distance: 'km ' + Math.floor(CFUtils.findDistanceWithTwins(this.routeData, {lng: coordinates[0], lat: coordinates[1]}).distance * 10) / 10,
                                        distanceValue: Math.floor(CFUtils.findDistanceWithTwins(this.routeData, {lng: coordinates[0], lat: coordinates[1]}).distance * 100) / 100,
                                        remoteness
                                    }
                                    if (this.map) entry.elevation = Math.floor(this.map.queryTerrainElevation(coordinates)) + 'm'
                                    else entry.elevation = '-'
                                    this.tableData.push(entry)
                                }
                            })
                            promises++
                            if (promises == promisesNumber) resolve(this.tableData)
                        })
                        this.tableEntries.push('water')
                    }

                    // Add konbinis
                    if (entriesToAdd.includes('konbinis') && !this.tableEntries.includes('konbinis')) {
                        promisesNumber++
                        fetch('/map/sources/compressed_sources/konbinis.geojson')
                        .then(async (data) => {
                            var geojson = await data.json()
                            geojson.features.forEach(feature => {
                                if (typeof feature.geometry.coordinates[0] == 'object') var coordinates = feature.geometry.coordinates[0]
                                else var coordinates = feature.geometry.coordinates
                                // Define remoteness
                                var remotenessValue = turf.pointToLineDistance(coordinates, this.routeData)
                                if (remotenessValue < 0.1) var remoteness = 'コース上'
                                else var remoteness = Math.floor(remotenessValue * 1000) + 'm'
                                // Define logo
                                var brand = 'その他'
                                for (const [brandName, searchNames] of Object.entries(this.konbiniSearchNames)) {
                                    searchNames.forEach(searchName => {
                                        if (feature.properties.name && feature.properties.name.includes(searchName)) brand = brandName
                                    })
                                }
                                if (brand != 'その他') var logo = '<img src="/map/media/_icon-' + brand + '.svg">'
                                else logo = '-'
                                // Define name
                                if (feature.properties.branch) var name = feature.properties.branch
                                else {
                                    switch (brand) {
                                        case 'seven-eleven': var name = 'セブンイレブン'; break
                                        case 'family-mart': var name = 'ファミリーマート'; break
                                        case 'lawson': var name = 'ローソン'; break
                                        case 'mini-stop': var name = 'ミニストップ'; break
                                        case 'daily-yamazaki': var name = 'デイリーヤマザキ'; break
                                    }
                                }
                                if (remotenessValue < 0.3) {
                                    let entry = {
                                        type: 'konbini',
                                        brand,
                                        lngLat: {lng: coordinates[0], lat: coordinates[1]},
                                        id: feature.properties.id.replace('/', ''),
                                        logo,
                                        name,
                                        geolocation: '-',
                                        distance: 'km ' + Math.floor(CFUtils.findDistanceWithTwins(this.routeData, {lng: coordinates[0], lat: coordinates[1]}).distance * 10) / 10,
                                        distanceValue: Math.floor(CFUtils.findDistanceWithTwins(this.routeData, {lng: coordinates[0], lat: coordinates[1]}).distance * 100) / 100,
                                        remoteness
                                    }
                                    if (this.map) entry.elevation = Math.floor(this.map.queryTerrainElevation(coordinates)) + 'm'
                                    else entry.elevation = '-'
                                    this.tableData.push(entry)
                                }
                            })
                            promises++
                            if (promises == promisesNumber) resolve(this.tableData)
                        })
                        this.tableEntries.push('konbinis')
                    }
                })
            }

            var tableData = await buildTableData()

            // Sort table entries
            tableData.sort((a,b) => a.distanceValue - b.distanceValue)

            // Build table
            var tbody = document.querySelector('#routeTable tbody')
            tbody.querySelector('.loader-center').remove() // Remove loader
            tbody.querySelectorAll('td').forEach(td => td.remove()) // Remove all existing data
            var previousEntry
            tableData.forEach((entry) => {

                var tr = document.createElement('tr')
                tr.classList.add('table-entry-' + entry.type)
                if (entry.remoteness != 'コース上') tr.classList.add('offroute')
                var td = []
                if (previousEntry && entry.distance == previousEntry.distance) var ignore = true // Ignore if similar entry
                tr.id = entry.type + entry.id
                // Create tds
                for (let i = 1; i <= 6; i++) {
                    td[i] = document.createElement('td')
                }
                // Populate tds
                td[1].innerHTML = entry.distance
                td[2].innerHTML = entry.logo
                td[3].innerHTML = entry.name
                td[4].innerHTML = entry.geolocation
                td[5].innerHTML = entry.elevation
                td[6].innerHTML = entry.remoteness
                // Style tds
                td[1].className = 'text-left'
                td[2].className = 'text-center'
                td[3].className = 'text-left'
                td[4].className = 'text-center'
                td[5].className = 'text-center'
                td[6].className = 'text-center'
                // Append tds
                for (let i = 1; i <= 6; i++) tr.appendChild(td[i])
                if (!ignore) tbody.appendChild(tr)
                previousEntry = entry

                // Set entry event listener
                tr.addEventListener('click', (e) => {
                    var target = e.target.closest('tr')
                    var $marker = document.querySelector('.mapboxgl-canvas-container #' + entry.type + entry.id)
                    // If clicked entry is not already selected
                    if (!target.classList.contains('selected-entry')) {
                        document.querySelector('#routeTable #' + entry.type + entry.id).classList.add('selected-entry')
                        // Toggle popup and add selected class to corresponding marker and table entry
                        if (this.map && $marker) this.map._markers.forEach( (marker) => {
                            var $marker = marker.getElement()
                            if (getIdFromString($marker.id) == entry.id || this.ride && (this.ride.options.sf == true && getIdFromString($marker.id) == 0 && entry.id == this.ride.checkpoints.length - 1)) {
                                marker.togglePopup()
                                $marker.classList.add('selected-marker')
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
                                if (marker.getPopup() && marker.getPopup().isOpen()) marker.getPopup().remove()
                                $marker.classList.remove('selected-marker')
                            }
                        } )
                        else if (this.map) this.setHighlightingLayer(entry.lngLat, 'toilets')
                        // Remove selected class from other thumbnails and table entries
                        document.querySelectorAll('.rt-preview-photo').forEach( (thumbnail) => {
                            if (thumbnail.id != entry.type + entry.id && (!this.ride || !(this.ride.options.sf == true && entry.id == this.ride.checkpoints.length - 1))) thumbnail.querySelector('img').classList.remove('selected-marker')
                        } )
                        document.querySelectorAll('#routeTable tr').forEach( (tableEntry) => {
                            if (tableEntry != target) tableEntry.classList.remove('selected-entry')
                        } )
                        if (document.querySelector('#displaySceneriesBox') && document.querySelector('#displaySceneriesBox').checked) {
                            // Fly to the marker location
                            this.map.flyTo( {
                                center: [entry.lngLat.lng, entry.lngLat.lat],
                                zoom: 14,
                                speed: 1.4,
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
                        if ($marker) {
                            if (this.ride && (this.ride.options.sf == true && entry.type == 'checkpoint' && entry.id == this.ride.checkpoints.length - 1)) { // Unselect start marker if click on goal
                                document.querySelector('.mapboxgl-canvas-container #' + entry.type + 0).classList.remove('selected-marker')
                            } else document.querySelector('.mapboxgl-canvas-container #' + entry.type + entry.id).classList.remove('selected-marker')
                            // Close corresponding popup
                            this.map._markers.forEach( (marker) => {
                                if (getIdFromString(marker.getElement().id) == entry.id || this.ride && (this.ride.options.sf == true && getIdFromString(marker.getElement().id) == 0 && entry.id == this.ride.checkpoints.length - 1)) {
                                    marker.togglePopup()
                                }
                            } )
                        }
                        this.removeHighlightingLayer()
                        // Focus
                        this.focus(this.map.getSource('route')._data)
                    }
                } )
            } )
            resolve(true)
        })
    }

    enableTableButtons () {
        document.querySelectorAll('.spec-table-buttons .mp-button').forEach(button => {
            button.removeAttribute('disabled')
            button.addEventListener('click', () => {
                button.setAttribute('disabled', 'disabled')
                // Empty table and set loader
                let tbody = document.querySelector('#routeTable tbody')
                tbody.querySelectorAll('td').forEach(td => td.remove())
                let loader = new CircleLoader(tbody)
                loader.start()
                this.buildTable([button.dataset.entry]).then(() => {
                    // Stop loader
                    loader.stop()
                })
            })
        })
    }

    buildSlider () {

        // Build variable
        var sliderData = []
                
        // Add each scenery
        for (let i = 0; i < this.mapdata.sceneries.length; i++) {
            if (this.mapdata.sceneries[i].on_route) {
                
                if (this.mapdata.sceneries[i].filename !== null) var thumbnailSrc = this.mapdata.sceneries[i].file_url
                else var thumbnailSrc = '/media/default-photo-' + Math.ceil(Math.random() * 9) + '.svg'
                let entry = {
                    type: 'scenery',
                    lngLat: {lng: this.mapdata.sceneries[i].lng, lat: this.mapdata.sceneries[i].lat},
                    id: this.mapdata.sceneries[i].id,
                    name: this.mapdata.sceneries[i].name,
                    distance: 'km ' + Math.floor(this.mapdata.sceneries[i].distance * 10) / 10,
                    distanceValue: this.mapdata.sceneries[i].distance, 
                    thumbnailSrc: this.mapdata.sceneries[i].file_url
                }
                sliderData.push(entry)
            }
        }

        // Add each checkpoint
        if (this.rideId) {
            for (let i = 0; i < this.ride.checkpoints.length; i++) {
                if (this.ride.checkpoints[i].img.filename) var thumbnailSrc = this.ride.checkpoints[i].img.url
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
                                if (marker.getPopup() && marker.getPopup().isOpen()) marker.getPopup().remove()
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
                    ride.checkpoints[ride.checkpoints.length - 1].distance = Math.floor(turf.length(this.routeData) * 10) / 10
                }
                else ride.options = {sf: false}
                this.ride = ride

                resolve()

                // Display ride checkpoints on the course
                this.displayCheckpoints(ride)
                this.profile.generate({
                    poiData: {
                        sceneries: this.mapdata.sceneries,
                        rideCheckpoints: this.ride.checkpoints
                    }
                })
            } )
        } )
    }

    displayCheckpoints () {
        this.ride.checkpoints.forEach( (checkpoint) => {
            if (this.ride.options.sf != true || checkpoint.number != this.ride.checkpoints.length - 1) checkpoint.marker = this.addMarker(checkpoint)
            // Remove sceneries in double
            this.mapdata.sceneries.forEach( (scenery) => {
                if (Math.ceil(checkpoint.distance * 100) / 100 == Math.ceil(scenery.distance * 100) / 100) {
                    document.querySelector('#scenery' + scenery.id).style.display = 'none'
                }
            } )
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
            this.profile.generate({
                poiData: {
                    sceneries: this.mapdata.sceneries,
                    rideCheckpoints: this.ride.checkpoints
                }
            })
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