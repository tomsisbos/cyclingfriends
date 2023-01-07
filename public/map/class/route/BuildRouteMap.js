import CFUtils from "/map/class/CFUtils.js"
import GlobalMap from "/map/class/GlobalMap.js"

export default class BuildRouteMap extends GlobalMap {

    constructor () {
        super ()
    }

    apiUrl = '/api/route.php'
    state = []
    currentState = 0
    buttonUndo
    buttonRedo
    waypointNumber = 0
    preparedWaypoint = 0
    start = 0
    endpointSet
    elevationProfile
    directionsMode = 'driving'
    centerOnUserLocation = () => this.map.setCenter(this.userLocation)

    addRouteControl () {
        // Get (or add) controller container
        if (document.querySelector('.map-controller')) var controller = document.querySelector('.map-controller')
        else var controller = this.addController()
        // Container
        var routeContainer = document.createElement('div')
        routeContainer.className = 'map-controller-block flex-column'
        controller.appendChild(routeContainer)
        // Label
        var routeOptionsLabel = document.createElement('div')
        routeOptionsLabel.innerText = 'ルート設定'
        routeOptionsLabel.className = 'map-controller-label'
        routeContainer.appendChild(routeOptionsLabel)
        // Line 3
        let line3 = document.createElement('div')
        line3.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line3)
        var boxShowDistanceMarkers = document.createElement('input')
        boxShowDistanceMarkers.id = 'boxShowDistanceMarkers'
        boxShowDistanceMarkers.setAttribute('type', 'checkbox')
        boxShowDistanceMarkers.setAttribute('checked', 'checked')
        line3.appendChild(boxShowDistanceMarkers)
        var boxShowDistanceMarkersLabel = document.createElement('label')
        boxShowDistanceMarkersLabel.innerText = '距離を表示'
        boxShowDistanceMarkersLabel.setAttribute('title', 'コース上に距離を表示する。ズームインすればするほど、細かく表示される。')
        boxShowDistanceMarkersLabel.setAttribute('for', 'boxShowDistanceMarkers')
        line3.appendChild(boxShowDistanceMarkersLabel)
        boxShowDistanceMarkers.addEventListener('change', () => {
            this.updateDistanceMarkers()
        } )
        // Line 4
        let line4 = document.createElement('div')
        line4.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line4)
        var boxSet3D = document.createElement('input')
        boxSet3D.id = 'boxSet3D'
        boxSet3D.setAttribute('type', 'checkbox')
        boxSet3D.setAttribute('checked', 'checked')
        line4.appendChild(boxSet3D)
        var boxSet3DLabel = document.createElement('label')
        boxSet3DLabel.innerText = '3次元'
        boxSet3DLabel.setAttribute('title', 'チェックすると、地形が3次元表示になる。Ctrlキーを押して、カメラを動かしてみよう。')
        boxSet3DLabel.setAttribute('for', 'boxSet3D')
        line4.appendChild(boxSet3DLabel)
        boxSet3D.addEventListener('change', () => {
            if (boxSet3D.checked) {
                this.map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 1})
            } else {
                this.map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 0})
            }
        } )
        // Camera buttons
        let line6 = document.createElement('div')
        line6.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line6)
        // Focus button
        var buttonFocus = document.createElement('button')
        buttonFocus.className = 'map-controller-block mp-button mp-button-small'
        buttonFocus.id = 'buttonFocus'
        buttonFocus.innerText = '全体表示'
        buttonFocus.setAttribute('title', 'ルート全体が地図の中央に表示されるようにカメラを調整する。')
        line6.appendChild(buttonFocus)
        buttonFocus.addEventListener('click', () => {
            this.focus(this.map.getSource('route')._data)
        } )
        // Fly button
        var buttonFly = document.createElement('button')
        buttonFly.className = 'map-controller-block mp-button mp-button-small'
        buttonFly.id = 'buttonFly'
        buttonFly.innerText = '走行再現'
        buttonFly.setAttribute('title', 'スタートからゴールまで、実際に走行しているかのようにコースを辿っていく。走行再現モードでコース全体のイメージを掴んでみよう。')
        line6.appendChild(buttonFly)
        buttonFly.addEventListener('click', () => {
            if (this.map.getSource('route')) {
                this.flyAlong(this.data.routeData)
            }
        } )
        // Edition buttons
        let line7 = document.createElement('div')
        line7.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line7)

        // Hide and open on click on mobile display
        routeOptionsLabel.addEventListener('click', () => {
            routeContainer.querySelectorAll('.map-controller-line').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    routeOptionsLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )
    }

    addRouteEditionControl () {
        // Get (or add) controller container
        if (document.querySelector('.map-controller')) var controller = document.querySelector('.map-controller')
        else var controller = this.addController()
        // Route options container
        var routeContainer = document.createElement('div')
        routeContainer.className = 'map-controller-block fullwidth flex-column'
        controller.appendChild(routeContainer)
        // Label
        var routeOptionsLabel = document.createElement('div')
        routeOptionsLabel.innerText = '作成ツール'
        routeOptionsLabel.className = 'map-controller-label'
        routeContainer.appendChild(routeOptionsLabel)
        // Line 1
        let line1 = document.createElement('div')
        line1.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line1)
        var modeSelectLabel = document.createElement('label')
        modeSelectLabel.innerText = '編集モード：'
        modeSelectLabel.setAttribute('title', 'ルート作成モードでは、地図上をクリックすると、ルートを延長することが出来る。ウェイポイント追加モードでは、ルート上にクリックしウェイポイントを追加することができる。')
        modeSelectLabel.setAttribute('for', 'boxAddWaypoints')
        line1.appendChild(modeSelectLabel)
        // Line 2
        let line2 = document.createElement('div')
        line2.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line2)
        this.modeSelect = document.createElement('select')
        this.modeSelect.id = "boxAddWaypoints"
        line2.appendChild(this.modeSelect)
        var modeSelectOption1 = document.createElement('option')
        modeSelectOption1.value = 'drawRoute'
        modeSelectOption1.innerText = 'ルート作成'
        modeSelectOption1.title = '地図上をクリックすると、ルートを延長することが出来る。'
        this.modeSelect.appendChild(modeSelectOption1)
        var modeSelectOption2 = document.createElement('option')
        modeSelectOption2.value = 'addWaypoints'
        modeSelectOption2.innerText = 'ウェイポイント追加'
        modeSelectOption2.title = 'ルート上にクリックしウェイポイントを追加することができる。'
        this.modeSelect.appendChild(modeSelectOption2)
        this.modeSelect.addEventListener('change', () => {
            console.log('boxAddWaypoints changed')
            this.setMode()
        } )
        // Line 3
        let line3 = document.createElement('div')
        line3.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line3)
        var boxFollowRoads = document.createElement('input')
        boxFollowRoads.id = 'boxFollowRoads'
        boxFollowRoads.setAttribute('type', 'checkbox')
        boxFollowRoads.setAttribute('checked', 'checked')
        line3.appendChild(boxFollowRoads)
        var boxFollowRoadsLabel = document.createElement('label')
        boxFollowRoadsLabel.innerText = '道路に沿って作る'
        boxFollowRoadsLabel.setAttribute('title', 'チェックすると、コース作成の際に最適な道を辿る。チェックを外すと、ウェイポイントを直線で結ぶ。')
        boxFollowRoadsLabel.setAttribute('for', 'boxFollowRoads')
        line3.appendChild(boxFollowRoadsLabel)
        // Line 4
        let line4 = document.createElement('div')
        line4.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line4)
        var boxFollowPaths = document.createElement('input')
        boxFollowPaths.id = 'boxFollowPaths'
        boxFollowPaths.setAttribute('type', 'checkbox')
        line4.appendChild(boxFollowPaths)
        var boxFollowPathsLabel = document.createElement('label')
        boxFollowPathsLabel.innerText = '小道を優先する'
        boxFollowPathsLabel.setAttribute('title', 'チェックすると、コースの際に小道等を含めた中で最短コースを辿る。細かい調整をする際に使ってみよう。')
        boxFollowPathsLabel.setAttribute('for', 'boxFollowPaths')
        line4.appendChild(boxFollowPathsLabel)
        boxFollowPaths.addEventListener('change', () => {
            if (boxFollowPaths.checked) this.directionsMode = 'cycling'
            else this.directionsMode = 'driving'
        } )
        // Edition buttons
        let line5 = document.createElement('div')
        line5.className = 'map-controller-buttons hide-on-mobiles'
        routeContainer.appendChild(line5)
        // Undo button
        this.buttonUndo = document.createElement('button')
        this.buttonUndo.className = 'map-controller-block mp-button mp-button-small'
        this.buttonUndo.id = 'buttonUndo'
        this.buttonUndo.innerHTML = '<span class="iconify" data-icon="mdi:undo"></span>'
        this.buttonUndo.setAttribute('title', '直前に行われた操作を取り消す。')
        line5.appendChild(this.buttonUndo)
        this.buttonUndo.addEventListener('click', async () => this.undo())
        this.buttonUndo.setAttribute('disabled', 'disabled')
        // Redo button
        this.buttonRedo = document.createElement('button')
        this.buttonRedo.className = 'map-controller-block mp-button mp-button-small'
        this.buttonRedo.id = 'buttonRedo'
        this.buttonRedo.innerHTML = '<span class="iconify" data-icon="mdi:redo"></span>'
        this.buttonRedo.setAttribute('title', '直前に取り消した処理をもう一度繰り返して実行する。')
        line5.appendChild(this.buttonRedo)
        this.buttonRedo.addEventListener('click', async () => this.redo())
        this.buttonRedo.setAttribute('disabled', 'disabled')
        let line6 = document.createElement('div')
        line6.className = 'map-controller-buttons hide-on-mobiles'
        routeContainer.appendChild(line6)
        // Clear button
        var buttonClear = document.createElement('button')
        buttonClear.className = 'map-controller-block mp-button mp-button-small'
        buttonClear.id = 'buttonClear'
        buttonClear.innerText = 'クリア'
        buttonClear.setAttribute('title', 'コースを削除し、白紙状態に戻す。')
        line6.appendChild(buttonClear)
        buttonClear.addEventListener('click', async () => {
            if (await openConfirmationPopup('現在のコースが削除されます。宜しいですか？')) {
                this.clearRoute()
                this.hideProfile()
            }                
        } )
        buttonClear.setAttribute('disabled', 'disabled')
        // Save button
        var buttonSave = document.createElement('button')
        buttonSave.className = 'map-controller-block mp-button mp-button-small'
        buttonSave.id = 'buttonSave'
        buttonSave.innerText = '保存'
        buttonSave.setAttribute('title', 'コースを保存し、マイルートページに戻る。')
        line6.appendChild(buttonSave)
        buttonSave.addEventListener('click', async () => {
            // Hide waypoints
            let i = 2
            while (this.map.getSource('wayPoint' + i)) {
                this.map.setLayoutProperty('wayPoint' + i, 'visibility', 'none')
                i++
            }
            // Center camera
            var routeBounds = CFUtils.defineRouteBounds(this.map.getSource('route')._data.geometry.coordinates)
            this.map.fitBounds(routeBounds)
            // Open save popup
            var answer = await this.openSavePopup()
            if (answer) {
                // Save canvas as a picture
                html2canvas(document.querySelector('.mapboxgl-canvas')).then( (canvas) => {
                    canvas.toBlob( async (blob) => {
                        answer.thumbnail = await blobToBase64(blob)
                        // When treatment is done, redirect to my routes page
                        this.saveRoute(answer)
                    }, 'image/jpeg', 0.7)
                } )            
            } else {
                // Restore waypoints
                let i = 2
                while (this.map.getSource('wayPoint' + i)) {
                    this.map.setLayoutProperty('wayPoint' + i, 'visibility', 'visible')
                    i++
                }
            }
        } )
        buttonSave.setAttribute('disabled', 'disabled')
        
        // Hide and open on click on mobile display
        routeOptionsLabel.addEventListener('click', () => {
            routeContainer.querySelectorAll('.map-controller-line, .map-controller-buttons').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    routeOptionsLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )

        // On map style change
        this.map.on('styledata', (e) => {
            // Disable clear, save and focus buttons if no route data displayed
            if (!e.target.style._layers.startPoint) {
                buttonClear.setAttribute('disabled', 'disabled')
                buttonFocus.setAttribute('disabled', 'disabled')
            } else {
                buttonClear.removeAttribute('disabled')
                buttonFocus.removeAttribute('disabled')
            }
            if (!e.target.style._layers.endPoint) {
                buttonSave.setAttribute('disabled', 'disabled')
                buttonFly.setAttribute('disabled', 'disabled')
            } else {
                buttonSave.removeAttribute('disabled')
                buttonFly.removeAttribute('disabled')
            }
        } )
    }

    async draw (end) {
        return new Promise(async (resolve, reject) => {
            var route = this.map.getSource('route')
            var boxFollowRoads = document.querySelector('#boxFollowRoads')
            // Choose drawing mode depending on settings
            if (boxFollowRoads.checked) await this.directionsRequest(route, end)
            else this.drawStraight(route, end)
            // Replace on the route if necessary
            if (route) {
                var point = this.map.getSource('endPoint')
                var correctedCoordinates = CFUtils.replaceOnRoute(point._data.features[0].geometry.coordinates, route._data)
                point._data.features[0].geometry.coordinates = correctedCoordinates
                point.setData(point._data)
            }
            resolve(true)
        } )
    }

    async edit (start, end, step = false) {
        return new Promise( async (resolve, reject) => {
            var response = {tunnels: [], section: []}
            // Get edited section data into response variable
            if (boxFollowRoads.checked) {
                response = await this.directionsRequestWithStep(start, end, step)
            } else {
                if (step) {
                    response.section = [step]
                } else {
                    response.section = [start]
                }
            }
            resolve (response)
        } )
    }

    // Make a directions request
    async directionsRequest (route, end) {
        // If the route already exists on the map, set previous end as start
        if (route) {
            var routeCoordinates = route._data.geometry.coordinates
            this.start = routeCoordinates[routeCoordinates.length - 1]
        }
        // Send request
        var tollOption = ''
        if (this.directionsMode == 'driving') tollOption = '&exclude=toll'
        const query = await fetch(`https://api.mapbox.com/directions/v5/mapbox/${this.directionsMode}/${this.start[0]},${this.start[1]};${end[0]},${end[1]}?geometries=geojson&steps=true&overview=full${tollOption}&access_token=${this.apiKey}`, {
            method: 'GET' }
        )
        console.log('MAPBOX DIRECTIONS API USE +1')
        // Prepare route features
        const json     = await query.json()
        const data     = json.routes[0]
        const section  = data.geometry.coordinates
        const geojson  = {
            type: 'Feature',
            properties: {
                tunnels: this.getTunnels(data)
            },
            geometry: {
                type: 'LineString',
                coordinates: section,
            }
        }
        
        // If the route already exists on the map, add new coordinates to it
        if (route) {
            // Get new coordinates array
            routeCoordinates = route._data.geometry.coordinates
            section.forEach( (coordinate) => {
                routeCoordinates.push(coordinate)
            } )
            geojson.geometry.coordinates = routeCoordinates
            // Get new tunnels array
            var tunnels = route._data.properties.tunnels
            if (!tunnels) {
                tunnels = []
            }
            this.getTunnels(data).forEach( (tunnel) => {
                if (!tunnels.includes(tunnel)) {
                    tunnels.push(tunnel)
                } else {
                    var index = tunnels.indexOf(tunnel)
                    if (index !== -1) {
                        tunnels.splice(index, 1)
                    }
                }
            } ) 
            geojson.properties.tunnels = tunnels
            // Add new coordinates to current route
            route.setData(geojson)
            // Update tunnels
            this.updateTunnels(tunnels)
            // Set starting point as a waypoint
            this.addWaypoint(this.waypointNumber, this.start)
        // Otherwise, draw the first route step
        } else {
            this.setFirstRouteStep(geojson)
            // Replace startPoint on route if necessary
            var startPoint = this.map.getSource('startPoint')
            startPoint._data.features[0].geometry.coordinates = CFUtils.replaceOnRoute(startPoint._data.features[0].geometry.coordinates, this.map.getSource('route')._data)
            startPoint.setData(startPoint._data)
            var endPoint = this.map.getSource('endPoint')
            endPoint._data.features[0].geometry.coordinates = CFUtils.replaceOnRoute(endPoint._data.features[0].geometry.coordinates, this.map.getSource('route')._data)
            endPoint.setData(endPoint._data)
        }
    }

    // Make a directions request with one step
    async directionsRequestWithStep (start, end, step = false) {
        // Send request
        var tollOption = ''
        if (this.directionsMode == 'driving') tollOption = '&exclude=toll'
        if (step) {
            var query = await fetch(`https://api.mapbox.com/directions/v5/mapbox/${this.directionsMode}/${start[0]},${start[1]};${step[0]},${step[1]};${end[0]},${end[1]}?geometries=geojson&steps=true&overview=full${tollOption}&access_token=${this.apiKey}`, {
            method: 'GET' } )            
        } else {
            var query = await fetch(`https://api.mapbox.com/directions/v5/mapbox/${this.directionsMode}/${start[0]},${start[1]};${end[0]},${end[1]}?geometries=geojson&steps=true&overview=full${tollOption}&access_token=${this.apiKey}`, {
            method: 'GET' } )
        }
        console.log('MAPBOX DIRECTIONS API USE +1')
        // Prepare route features
        const json     = await query.json()
        const data     = json.routes[0]
        var tunnels    = this.getTunnels(data) 
        const section  = data.geometry.coordinates
        // Remove first and last coordinate (corresponding to start and end)
        section.splice(section.length - 1, 1)
        section.splice(0, 1)
        return {tunnels: tunnels, section: section}
    }

    drawStraight (route, end) {
        // Prepare route features
        const geojson  = {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'LineString',
                coordinates: [this.start, end]
            }
        }
        // If the route already exists on the map, set previous end as start and add new coordinates to it
        if (route) {
            var coordinates = route._data.geometry.coordinates
            this.start = coordinates[coordinates.length - 1]
            coordinates.push(end)
            route.setData( {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: coordinates
                }
            } )
            // Set starting point as a waypoint
            this.addWaypoint(this.waypointNumber, this.start)
        } else { // otherwise, draw the first route step
            this.setFirstRouteStep(geojson)
        }
    }

    setFirstRouteStep (geojson) {
        this.addRouteLayer(geojson)
        this.updateTunnels(this.map.getSource('route')._data.properties.tunnels)
    }

    addWaypoint (number, coordinates) {
        // Add waypoint to the map
        this.map.addLayer( {
            id: 'wayPoint' + number,
            type: 'circle',
            source: {
                type: 'geojson',
                data: {
                    type: 'FeatureCollection',
                    features: [ {
                        type: 'Feature',
                        properties: {},
                        geometry: {
                            type: 'Point',
                            coordinates: coordinates
                        }
                    } ]
                }
            },
            paint: {
                'circle-radius': 4,
                'circle-color': 'white',
                'circle-stroke-color': this.routeColor,
                'circle-stroke-width': 1
            }
        } )
    }

    async removeWaypoint (thisWaypointNumber) {
        var route = this.map.getSource('route')
        var routeCoordinates = route._data.geometry.coordinates

        // Get previous and next waypoint coordinates
        if (thisWaypointNumber == 2) { // If moved waypoint number equals 2, previous waypoint is startPoint
            var previousWaypointCoordinates = this.map.getSource('startPoint')._data.features[0].geometry.coordinates
        } else {
            var previousWaypointCoordinates = this.map.getSource('wayPoint' + (thisWaypointNumber - 1))._data.features[0].geometry.coordinates
        }
        if (thisWaypointNumber == this.waypointNumber) { // If moved waypoint number equals current global number, next waypoint is endPoint
            var nextWaypointCoordinates = this.map.getSource('endPoint')._data.features[0].geometry.coordinates
        } else {
            var nextWaypointCoordinates = this.map.getSource('wayPoint' + (thisWaypointNumber + 1))._data.features[0].geometry.coordinates
        }

        // Get closest route coordinates corresponding to previous and next waypoint (in case of auto replacement on a road through Directions API)
        var closestPreviousWaypointCoordinates = CFUtils.closestLocation(previousWaypointCoordinates, routeCoordinates)
        var closestNextWaypointCoordinates = CFUtils.closestLocation(nextWaypointCoordinates, routeCoordinates)

        // Look for the route coordinates key corresponding to previous and next waypoint coordinates
        var startKey = parseInt(getKeyByValue(routeCoordinates, closestPreviousWaypointCoordinates))
        var endKey = parseInt(getKeyByValue(routeCoordinates, closestNextWaypointCoordinates))
        var toSlice = endKey - startKey - 1 // calculate the number of coordinates to slice from route coordinates array

        // Get edited section data
        var response = await this.edit(previousWaypointCoordinates, nextWaypointCoordinates)
        var section = response.section

        // Get section coords correctly sorted for loop adding
        section.reverse()
        routeCoordinates.splice(startKey + 1, toSlice)
        for (let i = 0; i < section.length; i++) {
            routeCoordinates.splice(startKey + 1, 0, section[i])
        }

        // Remove waypoint
        this.map.removeLayer('wayPoint' + thisWaypointNumber)
        this.map.removeSource('wayPoint' + thisWaypointNumber)

        // Update waypoints ID
        var storeWaypoints = []
        for (let i = thisWaypointNumber + 1; i <= this.waypointNumber; i++) {
            storeWaypoints[i] = this.map.getSource('wayPoint' + i)._data.features[0].geometry.coordinates
            this.map.removeLayer('wayPoint' + i)
            this.map.removeSource('wayPoint' + i)
        }
        for (let i = thisWaypointNumber + 1; i <= this.waypointNumber; i++) {
            this.map.addLayer( {
                id: 'wayPoint' + (i - 1),
                type: 'circle',
                source: {
                    type: 'geojson',
                    data: {
                        type: 'FeatureCollection',
                        features: [ {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'Point',
                                coordinates: storeWaypoints[i]
                            }
                        } ]
                    }
                },
                paint: {
                    'circle-radius': 4,
                    'circle-color': 'white',
                    'circle-stroke-color': this.routeColor,
                    'circle-stroke-width': 1
                }
            } )
        }
        this.waypointNumber--

        // Update route data
        const geojson = {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'LineString',
                coordinates: routeCoordinates
            }
        }
        // Get new tunnels array
        var tunnels = route._data.properties.tunnels
        if (!tunnels) {
            tunnels = []
        }
        response.tunnels.forEach( (tunnel) => {
            if (!tunnels.includes(tunnel)) {
                tunnels.push(tunnel)
            }
        } )
        geojson.properties.tunnels = tunnels
        route.setData(geojson)
        // Update tunnels
        this.updateTunnels(this.map.getSource('route')._data.properties.tunnels)

        console.log('State modified in removeWaypoint()')
        this.addState()
    }

    // Paint tunnels on map from an array of coordinate arrays
    updateTunnels (tunnels) {
        this.clearTunnels()
        if (tunnels) {
            var tunnelsToKeep = []
            tunnels.forEach( (tunnel) => {
                var toKeep
                // Check if tunnel is still located on the route
                tunnel.forEach( (coordinate) => {
                    if (this.map.getSource('route')._data.geometry.coordinates.includes(coordinate)) {
                        toKeep = true
                    }
                } )
                if (toKeep) tunnelsToKeep.push(tunnel)
            } )
            tunnelsToKeep.forEach( (tunnel) => {
                // Prepare layer data
                const tunnelData  = {
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'LineString',
                        coordinates: tunnel,
                    }
                }
                this.map.addLayer( {
                    id: 'tunnel' + this.tunnelNumber,
                    type: 'line',
                    source: {
                        type: 'geojson',
                        data: tunnelData
                    },
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'butt'
                    },
                    paint: {
                        'line-color': 'black',
                        'line-width': this.routeWidth,
                        'line-opacity': 1,
                    }
                } )
                this.tunnelNumber++
            } )
        }
        this.map.getSource('route')._data.properties.tunnels = tunnelsToKeep
    }

    async addIntermediateWaypoint (clickedCoordinates) {
        var route = this.map.getSource('route')
        var nearestPointOnLine = turf.nearestPointOnLine(route._data, turf.point(clickedCoordinates))
        var distanceFromStart = nearestPointOnLine.properties.location
        var closestPointOnRoute = turf.along(route._data, distanceFromStart)

        var previousWaypointNumber = this.findPreviousWaypointData(distanceFromStart, route._data)

        // Update waypoints ID
        var storeWaypoints = []
        for (let i = previousWaypointNumber + 1; i <= this.waypointNumber; i++) {
            if (this.waypointNumber >= 2) {
                storeWaypoints[i] = this.map.getSource('wayPoint' + i)._data.features[0].geometry.coordinates
                this.map.removeLayer('wayPoint' + i)
                this.map.removeSource('wayPoint' + i)
            }
        }
        for (let i = previousWaypointNumber + 1; i <= this.waypointNumber; i++) {
            if (this.waypointNumber >= 2) {
                var id = 'wayPoint' + (i + 1)
                this.map.addLayer( {
                    id: id,
                    type: 'circle',
                    source: {
                        type: 'geojson',
                        data: {
                            type: 'FeatureCollection',
                            features: [ {
                                type: 'Feature',
                                properties: {},
                                geometry: {
                                    type: 'Point',
                                    coordinates: storeWaypoints[i]
                                }
                            } ]
                        }
                    },
                    paint: {
                        'circle-radius': 4,
                        'circle-color': 'white',
                        'circle-stroke-color': this.routeColor,
                        'circle-stroke-width': 1
                    }
                } )
            }
        }
        this.waypointNumber = this.prepareNextWaypoint(this.waypointNumber)

        // Add waypoint
        this.addWaypoint(previousWaypointNumber + 1, closestPointOnRoute.geometry.coordinates)
    }

    // Get previous waypoint data
    findPreviousWaypointData (currentPosition, routeData) {
        // Get all waypoints distance from start into an array
        var i = 0
        var waypointsData = []
        while (this.map.getSource('wayPoint' + (i + 2))) {
            var waypointCoordinates = this.map.getSource('wayPoint' + (i + 2))._data.features[0].geometry.coordinates
            var nearestPointOnLine = turf.nearestPointOnLine(routeData, turf.point(waypointCoordinates))
            waypointsData.push( {
                id: i + 2,
                position: nearestPointOnLine.properties.location
            } )
            i++
        }

        // Compare current position to each waypoint and return the previous one on the line
        var closestWaypoint = {
            id: 1,
            position: 0
        }
        waypointsData.forEach( (waypointData) => {
            if (waypointData.position > closestWaypoint.position && waypointData.position < currentPosition) {
                closestWaypoint = waypointData
            }
        } )

        return closestWaypoint.id
    }

    prepareNextWaypoint (waypointNumber) {
        waypointNumber++
        // Make sure this waypoint will not be prepared more than once
        if (this.preparedWaypoint < waypointNumber) {
            // When pressing mouse on a waypoint
            this.map.on('mousedown', 'wayPoint' + waypointNumber, (e) => {
                // Get top waypoint
                const features = this.map.queryRenderedFeatures(e.point)
                var topWaypoint
                features.forEach( (feature) => {
                    if (!topWaypoint && feature.layer.id.includes('Point')) topWaypoint = feature.layer.id.match(/\d+/)[0]
                } )
                // Only set listener for top waypoint (and not waypoints below)
                if (topWaypoint == waypointNumber) {
                    // On right click
                    if (e.originalEvent.which != 3) {
                        e.preventDefault() // Prevent map from moving on grab
                        this.map.getCanvasContainer().style.cursor = 'grab'
                        var onMoveListener  = this.onMove.bind(this, waypointNumber)
                        var onUpListener    = this.onUp.bind(this, waypointNumber, onMoveListener) // Bind global this and listener for allowing listener removing
                        this.map.on('mousemove', onMoveListener)
                        this.map.once('mouseup', onUpListener)
                    // On left click
                    } else {
                        // When left click on a waypoint, remove it and recalculate
                        this.removeWaypoint(waypointNumber)
                    }
                }
            } )
            // When mouse enters a waypoint, prepare to drag    
            this.map.on('mouseenter', 'wayPoint' + waypointNumber, () => {
                this.map.setPaintProperty('route', 'line-opacity', 0.7)
                this.map.getCanvasContainer().style.cursor = 'move'
            } )
            this.map.on('mouseleave', 'wayPoint' + waypointNumber, () => {
                this.map.setPaintProperty('route', 'line-opacity', 1)
                if (!document.querySelector('#boxAddWaypoints').checked) this.map.getCanvasContainer().style.cursor = 'crosshair'
                else this.map.getCanvasContainer().style.cursor = ''
            } )
            this.preparedWaypoint++
        }
        return waypointNumber
    }

    // When moving the waypoint, change its coordinates
    onMove (waypointNumber, e) {
        // Change coordinates of moving waypoint. This refers to bound waypoint number
        var geojson = this.map.getSource('wayPoint' + waypointNumber)._data
        geojson.features[0].geometry.coordinates = [e.lngLat.lng, e.lngLat.lat]
        this.map.getSource('wayPoint' + waypointNumber).setData(geojson)
    }

    prepareOnMoveStartEndListeners (pointStatus) {
        var onMoveListener = this.onMoveStartEnd.bind(this, pointStatus) // Bind 'end' to onMoveStartEnd function and store it inside a variable
        var onUpListener   = this.onUpStartEnd.bind(this, pointStatus, onMoveListener) // Same for onUpfunction with variable containing listener
        this.map.on('mousemove', onMoveListener)
        this.map.once('mouseup', onUpListener)
    }

    configureStartPoint () {
        // When pressing mouse on startPoint
        this.map.on('mousedown', 'startPoint', (e) => {
            const features = this.map.queryRenderedFeatures(e.point)
            var isOtherPoint
            features.forEach( (feature) => {
                if (feature.layer.id.includes('wayPoint') || feature.layer.id.includes('endPoint')) isOtherPoint = true
            } )
            // Only set listener for startpoint if endpoint is not at the same place
            if (!isOtherPoint) {
                e.preventDefault() // Prevent map from moving on grab
                if (e.originalEvent.which != 3) { // If mousedown event is different from contextmenu
                    this.map.getCanvasContainer().style.cursor = 'grab'
                    this.prepareOnMoveStartEndListeners('start')
                } else {
                    if (!this.map.getSource('route')) {
                        this.clearRoute()
                        this.hideProfile()
                    }
                }
            }
        } )
        // When mouse enters startPoint, prepare to drag    
        this.map.on('mouseenter', 'startPoint', () => {
            if (this.map.getSource('route')) {
                this.map.setPaintProperty('route', 'line-opacity', 0.7)
            }
            this.map.getCanvasContainer().style.cursor = 'move'
        } )
        this.map.on('mouseleave', 'startPoint', () => {
            if (this.map.getSource('route')) {
                this.map.setPaintProperty('route', 'line-opacity', 1)
            }
            if (!document.querySelector('#boxAddWaypoints').checked) this.map.getCanvasContainer().style.cursor = 'crosshair'
            else this.map.getCanvasContainer().style.cursor = ''
        } )
    }

    configureEndPoint () {
        // When pressing mouse on endPoint
        this.map.on('mousedown', 'endPoint', (e) => {
            // Get top waypoint
            const features = this.map.queryRenderedFeatures(e.point)
            var isWaypoint
            features.forEach( (feature) => {
                if (feature.layer.id.includes('wayPoint')) isWaypoint = true
            } )
            if (!isWaypoint) {
                // If pressed key is not contextmenu
                if (e.originalEvent.which != 3) {
                    e.preventDefault() // Prevent map from moving on grab
                    this.map.getCanvasContainer().style.cursor = 'grab'
                    this.prepareOnMoveStartEndListeners('end')
                // If pressed key is contextmenu
                } else {
                    var route = this.map.getSource('route')
                    var routeCoordinates = route._data.geometry.coordinates
                    var tunnels = route._data.properties.tunnels
                    if (this.waypointNumber == 1) {
                        var previousWaypoint = this.map.getSource('startPoint')
                    } else {
                        var previousWaypoint = this.map.getSource('wayPoint' + this.waypointNumber)
                    }
                    var newEnd = previousWaypoint._data.features[0].geometry.coordinates
                    var data = previousWaypoint._data
                    // If waypoints remaining, replace previous waypoint by new endPoint
                    if (this.waypointNumber > 1) {
                        data.features[0].geometry.coordinates = newEnd
                        this.map.removeLayer('wayPoint' + this.waypointNumber)
                        this.map.removeSource('wayPoint' + this.waypointNumber)
                        this.map.getSource('endPoint').setData(data)
                        // Remove coordinates from route after new endPoint
                        var closestPreviousWaypointCoordinates = CFUtils.closestLocation(newEnd, routeCoordinates)
                        var endKey = parseInt(getKeyByValue(routeCoordinates, closestPreviousWaypointCoordinates))
                        routeCoordinates.splice(endKey + 1, routeCoordinates.length - endKey - 1)
                        // Update route data
                        const geojson = {
                            type: 'Feature',
                            properties: {
                                tunnels: tunnels
                            },
                            geometry: {
                                type: 'LineString',
                                coordinates: routeCoordinates
                            }
                        }
                        route.setData(geojson)
                        // Update tunnels
                        this.updateTunnels(route._data.properties.tunnels)
                    // Else, remove endPoint
                    } else {
                        this.map.removeLayer('endPoint')
                        this.map.removeSource('endPoint')
                        this.map.removeLayer('route')
                        this.map.removeSource('route')
                        for (let i = 0; i < this.tunnelNumber; i++) {
                            this.map.removeLayer('tunnel' + i)
                            this.map.removeSource('tunnel' + i)
                        }
                        this.start = this.map.getSource('startPoint')._data.features[0].geometry.coordinates
                        this.hideProfile()
                        this.hideDistanceMarkers()
                    }
                    this.addState()
                    console.log('State modified in configureEndPoint()')

                    this.waypointNumber--
                }
            }
        } )
        // When mouse enters endPoint, prepare to drag    
        this.map.on('mouseenter', 'endPoint', () => {
            if (this.map.getSource('Route')) {
                this.map.setPaintProperty('route', 'line-opacity', 0.7)
            }
            this.map.getCanvasContainer().style.cursor = 'move'
        } )
        this.map.on('mouseleave', 'endPoint', () => {
            if (this.map.getSource('route')) {
                this.map.setPaintProperty('route', 'line-opacity', 1)
            }
            if (!document.querySelector('#boxAddWaypoints').checked) this.map.getCanvasContainer().style.cursor = 'crosshair'
            else this.map.getCanvasContainer().style.cursor = ''
        } )
    }

    // When moving the point, change its coordinates
    onMoveStartEnd (pointStatus, e) {
        // Change coordinates of moving waypoint. This refers to bound waypoint number
        if (pointStatus == 'start') {
            var point = this.map.getSource('startPoint')
        } else if (pointStatus == 'end') {
            var point = this.map.getSource('endPoint')
        }
        var geojson = point._data
        geojson.features[0].geometry.coordinates = [e.lngLat.lng, e.lngLat.lat]
        point.setData(geojson)
    }

    // When dropping the waypoint, update route according to new coordinates
    async onUp (waypointNumber, onMoveListener) { // this refers to moved waypoint number
        this.map.getCanvasContainer().style.cursor  = '';
        var thisWaypoint     = this.map.getSource('wayPoint' + waypointNumber)
        var route            = this.map.getSource('route')
        var routeCoordinates = route._data.geometry.coordinates
        var tunnels          = route._data.properties.tunnels
         
        // Unbind mouse/touch events (using bound variable)
        this.map.off('mousemove', onMoveListener); 
        this.map.off('touchmove', onMoveListener);

        // Get next waypoint coordinates
        var thisWaypointCoordinates = thisWaypoint._data.features[0].geometry.coordinates
        if (waypointNumber == 2) { // If moved waypoint number equals 2, previous waypoint is startPoint
            var previousWaypointCoordinates = this.map.getSource('startPoint')._data.features[0].geometry.coordinates
        } else {
            var previousWaypointCoordinates = this.map.getSource('wayPoint' + (waypointNumber - 1))._data.features[0].geometry.coordinates
        }
        if (waypointNumber == this.waypointNumber) { // If moved waypoint number equals current global number, next waypoint is endPoint
            var nextWaypointCoordinates = this.map.getSource('endPoint')._data.features[0].geometry.coordinates
        } else {
            var nextWaypointCoordinates = this.map.getSource('wayPoint' + (waypointNumber + 1))._data.features[0].geometry.coordinates
        }

        // Get closest route coordinates corresponding to previous and next waypoint (in case of auto replacement on a road through Directions API)
        var closestPreviousWaypointCoordinates = CFUtils.closestLocation(previousWaypointCoordinates, routeCoordinates)
        var closestNextWaypointCoordinates = CFUtils.closestLocation(nextWaypointCoordinates, routeCoordinates)

        // Look for the route coordinates key corresponding to previous and next waypoint coordinates
        if (waypointNumber == 2) var startKey = 0
        else var startKey = parseInt(getKeyByValue(routeCoordinates, closestPreviousWaypointCoordinates))
        var endKey = parseInt(getKeyByValue(routeCoordinates, closestNextWaypointCoordinates))
        var toSlice = endKey - startKey - 1 // calculate the number of coordinates to slice from route coordinates array

        // Get edited section data
        var response = await this.edit(previousWaypointCoordinates, nextWaypointCoordinates, thisWaypointCoordinates)

        // Get section coords correctly sorted for loop adding
        var section = response.section
        section.reverse()
        routeCoordinates.splice(startKey + 1, toSlice)
        for (let i = 0; i < section.length; i++) {
            routeCoordinates.splice(startKey + 1, 0, section[i])
        }

        // Update route data
        const geojson = {
            type: 'Feature',
            properties: {},
            geometry: {
                type: 'LineString',
                coordinates: routeCoordinates
            }
        }
        // Replace waypoint on the route if necessary
        var correctedCoordinates = CFUtils.replaceOnRoute(thisWaypointCoordinates, route._data)
        thisWaypoint._data.features[0].geometry.coordinates = correctedCoordinates
        thisWaypoint.setData(thisWaypoint._data)
        // Get new tunnels array
        if (!tunnels) {
            tunnels = []
        }
        response.tunnels.forEach( (tunnel) => {
            if (!tunnels.includes(tunnel)) {
                tunnels.push(tunnel)
            }
        } )
        geojson.properties.tunnels = tunnels
        route.setData(geojson)
        // Update tunnels
        this.updateTunnels(route._data.properties.tunnels)
    }

    // When dropping the point, update route according to new coordinates
    async onUpStartEnd (pointStatus, onMoveListener, e) {

        // Unbind mouse/touch events (using bound variable)
        this.map.off('mousemove', onMoveListener)
        this.map.off('touchmove', onMoveListener)

        this.map.getCanvasContainer().style.cursor = ''
        if (pointStatus == 'start') var point = this.map.getSource('startPoint')
        else if (pointStatus == 'end') var point = this.map.getSource('endPoint')
        // Get previous and next waypoint coordinates (or closest coordinates)
        var pointCoordinates = point._data.features[0].geometry.coordinates

        if (this.map.getSource('route')) {
            var route = this.map.getSource('route')
            var routeCoordinates = route._data.geometry.coordinates
            if (pointStatus == 'start') { // If moved point is startPoint, get next waypoint (or endPoint if none) coordinates
                if (this.map.getSource('wayPoint2')) var nextWaypointCoordinates = this.map.getSource('wayPoint2')._data.features[0].geometry.coordinates
                else var nextWaypointCoordinates = this.map.getSource('endPoint')._data.features[0].geometry.coordinates
                var closestNextWaypointCoordinates = CFUtils.closestLocation(nextWaypointCoordinates, routeCoordinates)
                var startKey = 0
                var endKey = parseInt(getKeyByValue(routeCoordinates, closestNextWaypointCoordinates))
                var toSlice = endKey - startKey
                
                // Get edited section data
                var response = await this.edit(pointCoordinates, nextWaypointCoordinates)
                
                // Get section coords correctly sorted for loop adding
                var section = response.section
                section.reverse()
                routeCoordinates.splice(startKey, toSlice)
                for (let i = 0; i < section.length; i++) routeCoordinates.splice(startKey, 0, section[i])
            } else if (pointStatus == 'end') { // If moved point is endPoint, get previous waypoint (or startPoint if none) coordinates
                if (this.map.getSource('wayPoint' + (this.waypointNumber))) var previousWaypointCoordinates = this.map.getSource('wayPoint' + (this.waypointNumber))._data.features[0].geometry.coordinates
                else var previousWaypointCoordinates = this.map.getSource('startPoint')._data.features[0].geometry.coordinates
                var closestPreviousWaypointCoordinates = CFUtils.closestLocation(previousWaypointCoordinates, routeCoordinates)
                var startKey = parseInt(getKeyByValue(routeCoordinates, closestPreviousWaypointCoordinates))
                var endKey = routeCoordinates.length
                var toSlice = endKey - startKey

                // Get edited section data
                if (document.querySelector('#boxFollowRoads').checked) {
                    var response = await this.directionsRequestWithStep(closestPreviousWaypointCoordinates, pointCoordinates)
                    // Get section coords correctly sorted for loop adding
                    var section = response.section
                    section.reverse()
                    routeCoordinates.splice(startKey, toSlice)
                    for (let i = 0; i < section.length; i++) routeCoordinates.splice(startKey, 0, section[i])
                } else {
                    var section = [e.lngLat.lng, e.lngLat.lat]
                    routeCoordinates.splice(routeCoordinates.length - 1, 1, section)
                }
            }

            // Update route data
            const geojson = {
                type: 'Feature',
                properties: {},
                geometry: {
                    type: 'LineString',
                    coordinates: routeCoordinates
                }
            }
            // Replace on the route if necessary
            var correctedCoordinates = CFUtils.replaceOnRoute(pointCoordinates, route._data)
            point._data.features[0].geometry.coordinates = correctedCoordinates
            point.setData(point._data)
            // Get new tunnels array
            if (response) {
                var tunnels = route._data.properties.tunnels
                if (!tunnels) {
                    tunnels = []
                }
                response.tunnels.forEach( (tunnel) => {
                    if (!tunnels.includes(tunnel)) {
                        tunnels.push(tunnel)
                    }
                } )
                geojson.properties.tunnels = tunnels
            }
            route.setData(geojson)
            // Update tunnels
            this.updateTunnels(route._data.properties.tunnels)
        // Set start property to released point coordinates
        } else this.start = Object.keys(e.lngLat).map((key) => e.lngLat[key])

        this.addState()
        console.log('State modified in onUpStartEnd()')
    }

    async generateProfile (options = {force: false}) {
        
        const route = this.map.getSource('route')

        // Update profile tag
        this.updateProfileTag()

        // If a route is displayed on the map
        if (route) {

            // Prepare profile data
            var profileData = await this.getProfileData(route._data, {remote: true})
            
            // Draw profile inside elevationProfile element

            // Prepare profile settings
            const ctx = document.getElementById('elevationProfile').getContext('2d')
            const downtwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y + 2 ? value : undefined
            const flat = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 2 ? value : undefined
            const uptwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 6 ? value : undefined
            const upsix = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 10 ? value : undefined
            const upten = (ctx, value) => ctx.p0.parsed.y > 0 ? value : undefined                    
            const data = {
                labels: profileData.labels,
                datasets: [ {
                    data: profileData.pointData,
                    fill: {
                        target: 'origin',
                        above: '#fffa9ccc'
                    },
                    borderColor: '#bbbbff',
                    tension: 0.1
                } ],
            }
            const backgroundColor = {
                id: 'backgroundColor',
                beforeDraw: (chart) => {
                    const ctx = chart.canvas.getContext('2d')
                    ctx.save()
                    ctx.globalCompositeOperation = 'destination-over'
                    var lingrad = ctx.createLinearGradient(0, 0, 0, 150);
                    lingrad.addColorStop(0.5, '#fff');
                    lingrad.addColorStop(1, '#eee');
                    ctx.fillStyle = lingrad
                    ctx.fillRect(0, 0, chart.width, chart.height)
                    ctx.restore()
                }
            }
            const cursorOnHover = {
                id: 'cursorOnHover',
                afterEvent: (chart, args) => {
                    var e = args.event
                    if (e.type == 'mousemove' && args.inChartArea == true) {
                        // Get relevant data
                        const dataX        = chart.scales.x.getValueForPixel(e.x)
                        const routeData    = route._data
                        const distance     = Math.floor(dataX * 10) / 10
                        const maxDistance  = chart.scales.x._endValue
                        const altitude     = profileData.pointsElevation[distance * 10]
                        // Slope
                        if (profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
                            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - profileData.averagedPointsElevation[Math.floor(distance * 10)]
                        } else { // Only calculate on previous 100m for the last index (because no next index)
                            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10)] - profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
                        }
                        // As mouse is inside route profile area
                        if (distance >= 0 && distance <= maxDistance) {
                            // Reload canvas
                            this.elevationProfile.destroy()
                            this.elevationProfile = new Chart(ctx, chartSettings)
                            // Draw a line
                            ctx.strokeStyle = 'black'
                            ctx.lineWidth = 1
                            ctx.beginPath()
                            ctx.moveTo(e.x, 0)
                            ctx.lineTo(e.x, 9999)
                            ctx.stroke()
                            // Display corresponding point on route
                            var routePoint = turf.along(routeData, distance, {units: 'kilometers'})
                            if (slope <= 2 && slope >= -2) {
                                var circleColor = 'white'
                            } else {
                                var circleColor = this.setSlopeStyle(slope).color
                            }
                            if (!this.map.getLayer('profilePoint')) {
                                this.map.addLayer( {
                                    id: 'profilePoint',
                                    type: 'circle',
                                    source: {
                                        type: 'geojson',
                                        data: routePoint
                                    },
                                    paint: {
                                        'circle-radius': 5,
                                        'circle-color': circleColor
                                    }
                                } )
                            } else {
                                this.map.getSource('profilePoint').setData(routePoint)
                                this.map.setPaintProperty('profilePoint', 'circle-color', circleColor)
                            }
                            // Display tooltip
                            this.clearTooltip()
                            this.drawTooltip(routeData, routePoint.geometry.coordinates[0], routePoint.geometry.coordinates[1], e.native.x)
                        }    
                    } else if (e.type == 'mouseout' || args.inChartArea == false) {
                        // Clear tooltip if one
                        this.clearTooltip()
                        // Reload canvas
                        this.elevationProfile.destroy()
                        this.elevationProfile = new Chart(ctx, chartSettings)
                        // Remove corresponding point on route
                        if (this.map.getLayer('profilePoint')) {
                            this.map.removeLayer('profilePoint')
                            this.map.removeSource('profilePoint')
                        }
                    }  
                }              
            }
            const options = {
                parsing: false,
                animation: false,
                maintainAspectRatio: false,
                pointRadius: 0,
                pointHitRadius: 0,
                pointHoverRadius: 0,
                events: ['mousemove', 'mouseout'],
                segment: {
                    borderColor: ctx => downtwo(ctx, '#00e06e') || flat(ctx, 'yellow') || uptwo(ctx, 'orange') || upsix(ctx, '#ff5555') || upten(ctx, 'black'),
                },
                scales: {
                    x: {
                        type: 'linear',
                        bounds: 'data',
                        grid: {
                            color: '#00000000',
                            tickColor: 'lightgrey'
                        },
                        ticks: {
                            format: {
                                style: 'unit',
                                unit: 'kilometer'
                            },
                            autoSkip: true,
                            autoSkipPadding: 50,
                            maxRotation: 0
                        },
                        beginAtZero: true,
                    },
                    y: {
                        grid: {
                            borderDash: [5, 5],
                            drawTicks: false
                        },
                        ticks: {
                            format: {
                                style: 'unit',
                                unit: 'meter'
                            },
                            autoSkipPadding: 20,
                            padding: 8
                        }
                    }
                },
                interaction: {
                    mode: 'point',
                    axis: 'x',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: false,
                        labels: {
                            boxWidth: 100
                        }
                    },
                    // Define background color
                    backgroundColor: backgroundColor,
                    // Draw a vertical cursor on hover
                    cursorOnHover: cursorOnHover,
                    tooltip: {
                        enabled: false
                    },
                },
            }
            const chartSettings = {
                type: 'line',
                data: data,
                options: options,
                plugins: [backgroundColor, cursorOnHover]
            }

            // Reset canvas
            if (this.elevationProfile) {
                this.elevationProfile.destroy()
            }
            // Bound chart to canvas
            this.elevationProfile = new Chart(ctx, chartSettings)
        }

        // Destroy canvas on click on clear button
        document.querySelector('#buttonClear').addEventListener('click', () => {
            if (this.elevationProfile) {
                this.elevationProfile.destroy()
            }
        } )
    }

    // Toggle profile
    toggleProfile () {
        document.querySelector('#profileBox').classList.toggle('show-profile')
        this.updateProfileTag()
        if (document.querySelector('.show-profile')) {
            this.generateProfile()
        }
    }

    // Hide profile
    hideProfile () {
        document.querySelector('#profileBox').classList.remove('show-profile')
        document.querySelector('.map-profile-tag').classList.remove('cursor-pointer')
        document.querySelector('.map-profile-tag').innerText = 'No elevation data to display.'
    }

    // Update profile tag
    updateProfileTag () {
        // If there is elevation data, display relevant tag
        if (this.map.getSource('endPoint')) {
            document.querySelector('.map-profile-tag').classList.add('cursor-pointer')
            if (document.querySelector('#profileBox').classList.contains('show-profile')) {
                document.querySelector('.map-profile-tag').innerText = 'Hide profile ▼'
            } else {
                document.querySelector('.map-profile-tag').innerText = 'Show profile ▲'
            }
        // If there is no elevation data, display a message explaining it 
        } else {
            document.querySelector('.map-profile-tag').classList.remove('cursor-pointer')
            document.querySelector('.map-profile-tag').innerText = 'No elevation data to display.'
        }

    }

    async openSavePopup () {
        return new Promise ((resolve, reject) => {
            // Initialize data
            var data = {}
            data.category = 'route'
            // Build modal
            var modal = document.createElement('div')
            modal.classList.add('modal', 'd-flex')
            document.querySelector('body').appendChild(modal)
            var savePopup = document.createElement('div')
            savePopup.classList.add('popup')
            savePopup.innerHTML = `
            <div>
                <label>タイトル :</label>
                <input type="text" class="js-route-name fullwidth" />
                <label>詳細 :</label>
                <textarea class="js-route-description fullwidth"></textarea>
            </div>
            <div id="saveButtons" class="d-flex justify-content-between">
                <div id="cancel" class="mp-button bg-darkred text-white">
                    キャンセル
                </div>
                <div id="save" class="mp-button bg-darkgreen text-white">
                    保存
                </div>
            </div>`

            modal.appendChild(savePopup)
            var inputName        = document.querySelector('.js-route-name')
            var inputDescription = document.querySelector('.js-route-description')
            inputName.addEventListener('change', () => data.name = inputName.value)
            inputDescription.addEventListener('change', () => data.description = inputDescription.value)
            // Close on click outside popup
            modal.addEventListener('mousedown', (e) => {
                if (e.target == modal) {
                    modal.remove()
                    resolve(false)
                }
            })
            // On click on "Yes" button, close the popup and return true
            document.querySelector('#save').addEventListener('click', () => {
                modal.remove()
                resolve(data)
            } )
            // On click on "Cancel" button, close the popup and return false
            document.querySelector('#cancel').addEventListener('click', () => {
                modal.remove()
                resolve(false)
            } )

            // If user has administation rights, display create segment button 
            ajaxGetRequest ('/api/map.php' + "?get-session=true", async (session) => {
                if (session.rights == 'administrator') {
                    var createSegmentButton = document.createElement('button')
                    createSegmentButton.id = 'createSegment'
                    createSegmentButton.className = 'mp-button bg-admin'
                    createSegmentButton.innerText = 'セグメントを作成'
                    document.querySelector('#saveButtons').before(createSegmentButton)

                    // On click of create segment button, display create segment form
                    createSegmentButton.addEventListener('click', () => {
                        // Set data default properties
                        data.rank = 'local'
                        data.advised = 'off'
                        data.specs = {
                            offroad: 'off',
                            rindo: 'off',
                            cyclinglane: 'off',
                            cyclingroad: 'off'
                        }
                        data.tags = []
                        data.seasons = []
                        data.advice = {}
                        data.category = 'segment'
                        // Hide create segment button
                        createSegmentButton.style.display = 'none'
                        // Correct style top property of the popup
                        savePopup.style.top = 'calc(30% - 100px)'
                        savePopup.style.left = 'calc(50% - 20vw)'
                        savePopup.style.maxWidth = '40vw'
                        // Build tag checkboxes
                        var $tags = ''
                        this.tags.forEach(tag => {
                            $tags += `
                            <div class="mp-checkbox">
                                <input type="checkbox" data-name="` + tag + `" id="tag` + tag + `" class="js-segment-tag" />
                                <label for="tag` + tag + `">` + CFUtils.getTagString(tag) + `</label>
                            </div>
                            `
                        } )
                        // Create form
                        var createSegmentForm = document.createElement('div')
                        createSegmentForm.id = 'createSegmentForm'
                        createSegmentForm.className = 'bg-admin'
                        createSegmentForm.innerHTML = `
                        <h5>セグメントプロパティ</h5>
                        <p>
                            <label>規模 :</label>
                            <select id="rank" class="js-segment-rank fullwidth">
                                <option value="local" selected>Local</option>
                                <option value="regional">Regional</option>
                                <option value="national">National</option>
                            </select>
                            <div class="mp-checkbox">
                                <input type="checkbox" id="advised" class="js-segment-advised" />
                                <label for="advised">cyclingfriendsのおススメ</label>
                            </div>
                        </p>
                        <button id="addSeason" class="mp-button bg-white">「おススメ季節」を追加</button>
                        <button id="addAdvice" class="mp-button bg-white">「ポイント」を追加</button>
                        <div class="mb-2">
                            <label>特徴 :</label><br>
                            <div class="mp-checkbox">
                                <input type="checkbox" id="specOffroad" class="js-segment-spec-offroad" />
                                <label for="specOffroad">未舗装</label>
                            </div>
                            <div class="mp-checkbox">
                                <input type="checkbox" id="specRindo" class="js-segment-spec-rindo" />
                                <label for="specRindo">林道</label>
                            </div>
                            <div class="mp-checkbox">
                                <input type="checkbox" id="specCyclinglane" class="js-segment-spec-cyclinglane" />
                                <label for="specCyclinglane">サイクリングレーン</label>
                            </div>
                            <div class="mp-checkbox">
                                <input type="checkbox" id="specCyclingroad" class="js-segment-spec-cyclingroad" />
                                <label for="specCyclingroad">サイクリングロード</label>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label>タグ :</label><br>` + 
                                $tags + `
                        </div>`
                        createSegmentButton.after(createSegmentForm)

                        // On click on add season button
                        var cursor = -1
                        var addSeasonButton = document.querySelector('#addSeason')
                        addSeasonButton.addEventListener('click', () => {
                            // Set data default properties
                            cursor++
                            data.seasons[cursor] = {
                                start: [1, 1],
                                end: [3, 12],
                                description: ''
                            }
                            // Create form
                            var seasonSection = document.createElement('div')
                            seasonSection.id = 'period' + (cursor + 1)
                            seasonSection.className = 'rt-section js-segment-season-section'
                            seasonSection.innerHTML = `
                            <label class="js-segment-period-title">時期 n°` + (cursor + 1) + `</label>
                            <div class="d-flex">
                                <div class="col-md-6">
                                    <label>開始 :</label><br>
                                    <div class="d-flex justify-content-center">
                                        <select id="periodStart2">
                                            <option value="1" selected>1月</option>
                                            <option value="2">2月</option>
                                            <option value="3">3月</option>
                                            <option value="4">4月</option>
                                            <option value="5">5月</option>
                                            <option value="6">6月</option>
                                            <option value="7">7月</option>
                                            <option value="8">8月</option>
                                            <option value="9">9月</option>
                                            <option value="10">10月</option>
                                            <option value="11">11月</option>
                                            <option value="12">12月</option>
                                        </select>
                                        <select id="periodStart1">
                                            <option value="1" selected>上旬</option>
                                            <option value="2">中旬</option>
                                            <option value="3">下旬</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label>終了 :</label><br>
                                    <div class="d-flex justify-content-center">
                                        <select id="periodEnd2">
                                            <option value="1">1月</option>
                                            <option value="2">2月</option>
                                            <option value="3">3月</option>
                                            <option value="4">4月</option>
                                            <option value="5">5月</option>
                                            <option value="6">6月</option>
                                            <option value="7">7月</option>
                                            <option value="8">8月</option>
                                            <option value="9">9月</option>
                                            <option value="10">10月</option>
                                            <option value="11">11月</option>
                                            <option value="12" selected>12月</option>
                                        </select>
                                        <select id="periodEnd1">
                                            <option value="1">上旬</option>
                                            <option value="2">中旬</option>
                                            <option value="3" selected>下旬</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <label>紹介文 :</label>
                            <textarea class="js-segment-period-description fullwidth"></textarea>
                            <button id="removePeriod` + (cursor + 1) + `" class="mapboxgl-popup-close-button" type="button">×</button>`
                            addSeasonButton.before(seasonSection)

                            // On remove period
                            document.querySelector('#removePeriod' + (cursor + 1)).addEventListener('click', (e) => {
                                // Set variables
                                cursor--
                                let number = getIdFromString(e.target.id)
                                // Update data
                                for (let i = number; i < data.seasons.length; i++) data.seasons[i - 1] = data.seasons[i]
                                data.seasons.pop()
                                // Update elements
                                seasonSection.remove()
                                document.querySelectorAll('.js-segment-season-section').forEach( (section) => {
                                    let id = getIdFromString(section.id)
                                    if (id > number) {
                                        section.id = 'season' + (id - 1) // Decrease section id number
                                        section.querySelector('.js-segment-period-title').innerText = '時期 n°' + (id - 1) // Decrease section title number
                                        section.querySelector('.mapboxgl-popup-close-button').id = 'removePeriod' + (id - 1) // Decrease section remove button id number
                                    }
                                } )
                            } )

                            // Data treatment
                            var selectPeriodStart1     = seasonSection.querySelector('#periodStart1')
                            var selectPeriodStart2     = seasonSection.querySelector('#periodStart2')
                            var selectPeriodEnd1       = seasonSection.querySelector('#periodEnd1')
                            var selectPeriodEnd2       = seasonSection.querySelector('#periodEnd2')
                            var inputPeriodDescription = seasonSection.querySelector('.js-segment-period-description')
                            selectPeriodStart1.addEventListener('change', (e) => data.seasons[getIdFromString(e.target.closest('.js-segment-season-section').id) - 1].start[0] = selectPeriodStart1.value)
                            selectPeriodStart2.addEventListener('change', (e) => data.seasons[getIdFromString(e.target.closest('.js-segment-season-section').id) - 1].start[1] = selectPeriodStart2.value)
                            selectPeriodEnd1.addEventListener('change', (e) => data.seasons[getIdFromString(e.target.closest('.js-segment-season-section').id) - 1].end[0] = selectPeriodEnd1.value)
                            selectPeriodEnd2.addEventListener('change', (e) => data.seasons[getIdFromString(e.target.closest('.js-segment-season-section').id) - 1].end[1] = selectPeriodEnd2.value)
                            inputPeriodDescription.addEventListener('change', (e) => data.seasons[getIdFromString(e.target.closest('.js-segment-season-section').id) - 1].description = inputPeriodDescription.value)
                        } )

                        // On click on add advice button
                        var addAdviceButton = document.querySelector('#addAdvice')
                        addAdviceButton.addEventListener('click', () => {
                            // Set data default properties
                            data.advice = {
                                name: '',
                                description: ''
                            }
                            // Create form
                            var adviceSection = document.createElement('div')
                            adviceSection.id = 'advice'
                            adviceSection.className = 'rt-section js-segment-advice-section'
                            adviceSection.innerHTML = `
                            <label>ポイント</label><br>
                            <label>タイトル :</label><br>
                            <input type="text" class="js-segment-advice-name fullwidth" />
                            <label>内容 :</label><br>
                            <textarea class="js-segment-advice-description fullwidth"></textarea>
                            <button id="removeAdvice" class="mapboxgl-popup-close-button" type="button">×</button>`
                            addSeasonButton.after(adviceSection)
                            // Hide add advice button
                            addAdviceButton.style.display = 'none'

                            // On remove advice
                            document.querySelector('#removeAdvice').addEventListener('click', () => {
                                // Update elements
                                adviceSection.remove()
                                addAdviceButton.style.display = 'inline-block'
                                // Update data
                                adviceSection = {}
                            } )

                            // Data treatment
                            var inputAdviceName        = document.querySelector('#advice .js-segment-advice-name')
                            var inputAdviceDescription = document.querySelector('#advice .js-segment-advice-description')
                            inputAdviceName.addEventListener('change', () => data.advice.name = inputAdviceName.value)
                            inputAdviceDescription.addEventListener('change', () => data.advice.description = inputAdviceDescription.value)
                        } )

                        // Display save as route button
                        var saveAsRouteButton = document.createElement('button')
                        saveAsRouteButton.id = 'saveAsRoute'
                        saveAsRouteButton.className = 'mp-button bg-darkred text-white'
                        saveAsRouteButton.innerText = '閉じる'
                        createSegmentForm.appendChild(saveAsRouteButton)
                        saveAsRouteButton.addEventListener('click', () => {
                            // Restore usual properties
                            data.category = 'route'
                            delete data.rank
                            delete data.advised
                            delete data.specs
                            delete data.tags
                            delete data.seasons
                            delete data.advice
                            createSegmentButton.style.display = 'inline-block'
                            savePopup.style.top = 'calc(50% - 100px)'
                            savePopup.style.left = 'calc(50% - 125px)'
                            savePopup.style.maxWidth = '250px'
                            createSegmentForm.remove()
                        } )

                        // Data treatment
                        var selectRank            = document.querySelector('.js-segment-rank')
                        var inputAdvised          = document.querySelector('.js-segment-advised')
                        var inputSpecOffroad      = document.querySelector('.js-segment-spec-offroad')
                        var inputSpecRindo        = document.querySelector('.js-segment-spec-rindo')
                        var inputSpecCyclinglane  = document.querySelector('.js-segment-spec-cyclinglane')
                        var inputSpecCyclingroad  = document.querySelector('.js-segment-spec-cyclingroad')
                        var inputSpecRindo        = document.querySelector('.js-segment-spec-rindo')
                        var inputTags             = document.querySelectorAll('.js-segment-tag')
                        selectRank.addEventListener('change', () => data.rank = selectRank.value)
                        inputAdvised.addEventListener('change', () => data.advised = inputAdvised.value)
                        inputSpecOffroad.addEventListener('change', () => data.specs.offroad = inputSpecOffroad.value)
                        inputSpecRindo.addEventListener('change', () => data.specs.rindo = inputSpecRindo.value)
                        inputSpecCyclinglane.addEventListener('change', () => data.specs.cyclinglane = inputSpecCyclinglane.value)
                        inputSpecCyclingroad.addEventListener('change', () => data.specs.cyclingroad = inputSpecCyclingroad.value)
                        inputTags.forEach( (inputTag) => {
                            inputTag.addEventListener('change', () => {
                                if (inputTag.value == 'on') data.tags.push(inputTag.dataset.name)
                                else if (inputTag.value == 'off') data.tags.splice(data.tags.indexOf(inputTag.dataset.name), 1)
                            } )
                        } )
                    } )
                }
            } )
        } )
    }

    setMode () {
        if (this.modeSelect.value == 'addWaypoints') {
            
            this.map.getCanvas().style.cursor = 'grab'
            this.map.on('mouseenter', 'route', () => {
                this.map.getCanvas().style.cursor = 'crosshair'
            } )
            this.map.on('mouseleave', 'route', () => {
                if (this.modeSelect.value == 'addWaypoints') {
                    this.map.getCanvas().style.cursor = 'grab'
                }
            } )
            this.map.off('click', this.routeBuilding)
            this.map.on('click', 'route', this.routeEditing)
        } else if (this.modeSelect.value == 'drawRoute') {
            this.map.getCanvasContainer().style.cursor = 'crosshair'
            this.map.off('click', 'route', this.routeEditing)
            this.map.on('click', this.routeBuilding)
        }
    }

    routeBuilding = async (e) => {
        // If starting point is already defined, request a route to clicked point
        if (this.start) {
            this.waypointNumber = this.prepareNextWaypoint(this.waypointNumber)
            const coords = Object.keys(e.lngLat).map((key) => e.lngLat[key])
            const end = {
                type: 'FeatureCollection',
                features: [ {
                    type: 'Feature',
                    properties: {},
                    geometry: {
                        type: 'Point',
                        coordinates: coords
                    }
                } ]
            }
            if (this.map.getLayer('endPoint')) this.map.getSource('endPoint').setData(end);
            else {
                this.map.addLayer({
                    id: 'endPoint',
                    type: 'circle',
                    source: {
                        type: 'geojson',
                        data: {
                            type: 'FeatureCollection',
                            features: [ {
                                type: 'Feature',
                                properties: {},
                                geometry: {
                                    type: 'Point',
                                    coordinates: coords
                                }
                            } ]
                        }
                    },
                    paint: {
                    'circle-radius': 8,
                    'circle-color': '#ff5555',
                    'circle-stroke-color': this.routeColor,
                    'circle-stroke-width': 2
                    }
                } )
                if (!this.endpointSet) {
                    this.configureEndPoint()
                }
                this.endpointSet = true
            }
            await this.draw(coords)
        // If first click on the map, define starting point
        } else if (!this.map.getSource('startPoint')) {
            this.start = Object.keys(e.lngLat).map((key) => e.lngLat[key])
            // Add starting point to the map
            this.map.addLayer( {
                id: 'startPoint',
                type: 'circle',
                source: {
                    type: 'geojson',
                    data: {
                        type: 'FeatureCollection',
                        features: [ {
                            type: 'Feature',
                            properties: {},
                            geometry: {
                                type: 'Point',
                                coordinates: this.start
                            }
                        } ]
                    }
                },
                paint: {
                    'circle-radius': 8,
                    'circle-color': '#afffaa',
                    'circle-stroke-color': this.routeColor,
                    'circle-stroke-width': 2
                }
            } )
            this.configureStartPoint()
        }
        this.addState()
        console.log('State modified in routeBuilding()')
    }

    routeEditing = (e) => {
        this.addIntermediateWaypoint([e.lngLat.lng, e.lngLat.lat])
        this.addState()
        console.log('State modified in routeEditing()')
    }

    async addState () {
        // Get startpoint source
        var startpoint = JSON.parse(JSON.stringify(this.map.getSource('startPoint')._data))
        // Get waypoint sources
        var waypoints = []
        if (this.map.getSource('wayPoint2')) {
            for (let i = 1; i < this.waypointNumber; i++) {
                if (this.map.getSource('wayPoint' + (i + 1))) waypoints.push(this.map.getSource('wayPoint' + (i + 1))._data)
            }
        }
        // Get route source
        if (this.map.getSource('route')) var route = JSON.parse(JSON.stringify(this.map.getSource('route')._data))
        else var route = {}
        // Get endpoint source
        if (this.map.getSource('endPoint')) var endpoint = JSON.parse(JSON.stringify(this.map.getSource('endPoint')._data))
        else var endpoint = {}

        // Store new state
        this.state.splice(this.currentState, Infinity, {startpoint, waypoints, endpoint, route})
        
        // Update current state property
        this.currentState++

        this.updateStateButtons()
    }

    restoreState (stateNumber) {

        var key = stateNumber - 1
        // Restore startpoint previous state
        this.map.getSource('startPoint').setData(JSON.parse(JSON.stringify(this.state[key].startpoint)))
        // Restore route previous state
        const route = JSON.parse(JSON.stringify(this.state[key].route))
        if (route.geometry) {
            if (this.map.getSource('route')) this.map.getSource('route').setData(route)
            else this.addRouteLayer(JSON.parse(JSON.stringify(this.state[key].route)))
        } else {
            if (this.map.getLayer('route')) {
                this.map.removeLayer('route')
                this.map.removeSource('route')
                this.hideProfile()
                this.hideDistanceMarkers()
            }
        }
        // Restore waypoints previous state
        for (let i = 0; i < this.waypointNumber - 1; i++) {
            this.map.removeLayer('wayPoint' + (i + 2))
            this.map.removeSource('wayPoint' + (i + 2))
        }
        for (let i = 0; i < this.state[key].waypoints.length; i++) {
            this.map.addSource('wayPoint' + (i + 2), {
                type: 'geojson',
                data: this.state[key].waypoints[i]
            } )
            this.map.addLayer( {
                id: 'wayPoint' + (i + 2),
                type: 'circle',
                source: 'wayPoint' + (i + 2),
                paint: {
                    'circle-radius': 4,
                    'circle-color': '#fff',
                    'circle-stroke-color': this.routeColor,
                    'circle-stroke-width': 1
                }
            } )
        }
        this.waypointNumber = this.state[key].waypoints.length + 1
        // Restore endpoint previous state
        const endpoint = this.state[key].endpoint
        if (this.map.getSource('endPoint')) {
            if (endpoint.type) this.map.getSource('endPoint').setData(endpoint)
            else {
                this.map.removeLayer('endPoint')
                this.map.removeSource('endPoint')
                ///this.start = this.map.getSource('startPoint')._data.features[0].geometry.coordinates
            }
        } else {
            this.map.addSource('endPoint', {
                type: 'geojson',
                data: endpoint
            } )
            this.map.addLayer( {
                id: 'endPoint',
                type: 'circle',
                source: 'endPoint',
                paint: {
                    'circle-radius': 8,
                    'circle-color': '#ff5555',
                    'circle-stroke-color': this.routeColor,
                    'circle-stroke-width': 2
                }
            } )
        }

        this.updateStateButtons()
    }

    updateStateButtons () {
        console.log('state length : ' + this.state.length)
        console.log('current state : ' + this.currentState)
        // Update undo/redo buttons depending on the state
        if (this.state.length == 1) {
            this.buttonUndo.setAttribute('disabled', 'disabled')
            this.buttonRedo.setAttribute('disabled', 'disabled')
        } else if (this.currentState > 1 && this.currentState != this.state.length) {
            this.buttonUndo.removeAttribute('disabled')
            this.buttonRedo.removeAttribute('disabled')
        } else if (this.currentState > 1 && this.currentState == this.state.length) {
            this.buttonUndo.removeAttribute('disabled')
            this.buttonRedo.setAttribute('disabled', 'disabled')
        } else if (this.currentState == 1 && this.state.length > 1) {
            this.buttonUndo.setAttribute('disabled', 'disabled')
            this.buttonRedo.removeAttribute('disabled')
        }
    }

    undo () {
        this.currentState--
        this.restoreState(this.currentState)
    }

    redo () {
        this.currentState++
        this.restoreState(this.currentState)
    }

    // Save current route
    async saveRoute (details) {
        // Reduce coordinates number for performance purpose
        if (this.map.getSource('route')._data.geometry.coordinates.length > 600) var routeData = turf.simplify(this.map.getSource('route')._data, {tolerance: 0.0001, highQuality: true, mutate: false})
        else var routeData = this.map.getSource('route')._data
        if (details.category == 'route') {
            var route = {
                id: 'new',
                type: routeData.geometry.type,
                coordinates: routeData.geometry.coordinates,
                tunnels: routeData.properties.tunnels,
                category: 'route',
                name: details.name,
                description: details.description,
                distance: turf.length(routeData),
                elevation: await this.calculateElevation(routeData),
                startplace: await this.getCourseGeolocation(routeData.geometry.coordinates[0]),
                goalplace: await this.getCourseGeolocation(routeData.geometry.coordinates[routeData.geometry.coordinates.length - 1]),
                thumbnail: details.thumbnail
            }
        } else if (details.category == 'segment') {
            var route = {
                id: 'new',
                type: routeData.geometry.type,
                coordinates: routeData.geometry.coordinates,
                tunnels: routeData.properties.tunnels,
                category: 'segment',
                name: details.name,
                description: details.description,
                distance: turf.length(routeData),
                elevation: await this.calculateElevation(routeData),
                startplace: await this.getCourseGeolocation(routeData.geometry.coordinates[0]),
                goalplace: await this.getCourseGeolocation(routeData.geometry.coordinates[routeData.geometry.coordinates.length - 1]),
                thumbnail: details.thumbnail,
                rank: details.rank,
                advised: details.advised,
                seasons: details.seasons,
                advice: details.advice,
                specs: details.specs,
                tags: details.tags
            }
        }
        ajaxJsonPostRequest(this.apiUrl, route, (response) => {
            console.log(response)
            if (route.category == 'segment') window.location.replace('/world')
            else window.location.replace('/' + this.session.login + '/routes')
        } )
    }

}