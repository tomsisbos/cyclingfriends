import CFUtils from "/map/class/CFUtils.js"
import Model from "/map/class/Model.js"
import MkpointPopup from "/map/class/MkpointPopup.js"
import tilebelt from "/node_modules/@mapbox/tilebelt/index.js"
import cover from '/node_modules/@mapbox/tile-cover/index.js'

// Global class initialization
export default class GlobalMap extends Model {

    constructor () {
        super()
        this.setSeason()
        ajaxGetRequest (this.apiUrl + "?get-session=true", (session) => {
            this.session = session
            sessionStorage.setItem('session-id', session.id)
            sessionStorage.setItem('session-login', session.login)
        } )
        ajaxGetRequest ('/api/riders/location.php' + "?get-location=true", (userLocation) => {
            if (userLocation.lng !== 0) this.userLocation = userLocation
            else this.userLocation = this.defaultCenter
            this.centerOnUserLocation()

        } )
        ajaxGetRequest (this.apiUrl + "?get-user-cleared-mkpoints=true", (mkpoints) => this.clearedMkpoints = mkpoints)
    }

    map
    $map
    loaded = false
    defaultCenter = [138.69056, 35.183002]
    defaultZoom = 10
    userLocation
    mkpoints
    tunnelNumber = 0
    profileData
    month = new Date().getMonth() + 1
    season
    routeColor = 'blue'
    routeCapColor = 'white'
    routeWidth = 5
    segmentLocalColor = '#8bffff'
    segmentRegionalColor = '#2bffff'
    segmentNationalColor = '#2bc8ff'
    segmentCapColor = 'white'

    setSeason () {
        if (this.month == 12 || this.month == 1 || this.month == 2) {
            this.season = 'winter'
        } else if (this.month == 3 || this.month == 4 || this.month == 5) {
            this.season = 'spring'
        } else if (this.month == 6 || this.month == 7 || this.month == 8) {
            this.season = 'summer'
        } else if (this.month == 9 || this.month == 10 || this.month == 11) {
            this.season = 'fall'
        } else {
            this.season = 'unknown'
        }
    }

    addController () {
        var controller = document.createElement('div')
        controller.className = 'map-controller map-controller-left'
        this.$map.appendChild(controller)
        return controller
    }

    addStyleControl () {
        // Get (or add) controller container
        if (document.querySelector('.map-controller')) var controller = document.querySelector('.map-controller')
        else var controller = this.addController()
        // Add style control
        var selectStyleContainer = document.createElement('div')
        selectStyleContainer.className = 'map-controller-block bold'
        controller.appendChild(selectStyleContainer)
        var selectStyleLabel = document.createElement('div')
        selectStyleLabel.innerText = 'Map style : '
        selectStyleContainer.appendChild(selectStyleLabel)
        var selectStyle = document.createElement('select')
        var seasons = document.createElement("option")
        var satellite = document.createElement("option")
        seasons.value, seasons.text = 'Seasons'
        seasons.setAttribute('selected', 'selected')
        seasons.id = 'cl07xga7c002616qcbxymnn5z'
        satellite.id = 'cl0chu1or003a15nocgiodiir'
        satellite.value, satellite.text = 'Satellite'
        selectStyle.add(seasons)
        selectStyle.add(satellite)
        selectStyle.className = 'js-map-styles'
        selectStyleContainer.appendChild(selectStyle)
        selectStyle.onchange = (e) => {
            var index = e.target.selectedIndex
            var layerId = e.target.options[index].id
            if (layerId === 'seasons') {
                const month = new Date().getMonth() + 1
                layerId = setSeasonLayer(month)
            }
            this.setMapStyle(layerId)
            this.generateProfile()
        }
    }

    addOptionsControl () {
        // Get (or add) controller container
        if (document.querySelector('.map-controller')) var controller = document.querySelector('.map-controller')
        else var controller = this.addController()
        // Add style control
        // Map options container
        var mapContainer = document.createElement('div')
        mapContainer.className = 'map-controller-block flex-column'
        controller.appendChild(mapContainer)
        // Label
        var mapOptionsLabel = document.createElement('div')
        mapOptionsLabel.innerText = 'Map options'
        mapOptionsLabel.className = 'map-controller-label'
        mapContainer.appendChild(mapOptionsLabel)
        // Line 1
        let line1 = document.createElement('div')
        line1.className = 'map-controller-line hide-on-mobiles'
        mapContainer.appendChild(line1)
        this.dislayKonbinisBox = document.createElement('input')
        this.dislayKonbinisBox.id = 'dislayKonbinisBox'
        this.dislayKonbinisBox.setAttribute('type', 'checkbox')
        this.dislayKonbinisBox.setAttribute('checked', 'true')
        line1.appendChild(this.dislayKonbinisBox)
        this.dislayKonbinisBox.addEventListener('click', () => {
            if (this.dislayKonbinisBox.checked) this.addKonbiniLayers()
            else this.hideKonbiniLayers()
        } )
        var dislayKonbinisBoxLabel = document.createElement('label')
        dislayKonbinisBoxLabel.setAttribute('for', 'dislayKonbinisBox')
        dislayKonbinisBoxLabel.innerText = 'Display konbinis'
        line1.appendChild(dislayKonbinisBoxLabel)
        // Line 2
        let line2 = document.createElement('div')
        line2.className = 'map-controller-line hide-on-mobiles'
        mapContainer.appendChild(line2)
        this.displayAmenitiesBox = document.createElement('input')
        this.displayAmenitiesBox.id = 'displayAmenitiesBox'
        this.displayAmenitiesBox.setAttribute('type', 'checkbox')
        this.displayAmenitiesBox.setAttribute('checked', 'true')
        line2.appendChild(this.displayAmenitiesBox)
        this.displayAmenitiesBox.addEventListener('click', () => {
            if (this.displayAmenitiesBox.checked) this.addAmenityLayers()
            else this.hideAmenityLayers()
        } )
        var displayAmenitiesBoxLabel = document.createElement('label')
        displayAmenitiesBoxLabel.setAttribute('for', 'displayAmenitiesBox')
        displayAmenitiesBoxLabel.innerText = 'Display amenities'
        line2.appendChild(displayAmenitiesBoxLabel)
        
        // Hide and open on click on mobile display
        mapOptionsLabel.addEventListener('click', () => {
            mapContainer.querySelectorAll('.map-controller-line').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    mapOptionsLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )
    }

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
        routeOptionsLabel.innerText = 'Route options'
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
        var boxShowDistanceMarkersLabel = document.createElement('div')
        boxShowDistanceMarkersLabel.innerText = 'Show distance markers'
        line3.appendChild(boxShowDistanceMarkersLabel)
        boxShowDistanceMarkers.addEventListener('change', () => {
            this.updateDistanceMarkers()
        } )
        // Line 4
        let line4 = document.createElement('div')
        line4.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line4)
        var boxSet3D = document.createElement('input')
        boxSet3D.setAttribute('type', 'checkbox')
        boxSet3D.setAttribute('checked', 'checked')
        line4.appendChild(boxSet3D)
        var boxSet3DLabel = document.createElement('div')
        boxSet3DLabel.innerText = 'Enable 3D'
        line4.appendChild(boxSet3DLabel)
        boxSet3D.addEventListener('change', () => {
            if (boxSet3D.checked) {
                this.map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 1})
            } else {
                this.map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 0})
            }
        } )
        // Line 5
        let line5 = document.createElement('div')
        line5.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line5)
        var boxShowMkpoints = document.createElement('input')
        boxShowMkpoints.id = 'boxShowMkpoints'
        boxShowMkpoints.setAttribute('type', 'checkbox')
        boxShowMkpoints.setAttribute('checked', 'checked')
        line5.appendChild(boxShowMkpoints)
        var boxShowMkpointsLabel = document.createElement('div')
        boxShowMkpointsLabel.innerText = 'Show scenery points'
        line5.appendChild(boxShowMkpointsLabel)
        boxShowMkpoints.addEventListener('change', () => {
            if (boxShowMkpoints.checked) {
                this.addMkpoints(this.mkpoints)
                document.querySelector('.rt-slider').style.display = 'flex'
                this.generateProfile()
            } else {
                this.hideMkpoints()
                document.querySelector('.rt-slider').style.display = 'none'
                this.generateProfile()
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
        buttonFocus.innerText = 'Focus'
        line6.appendChild(buttonFocus)
        buttonFocus.addEventListener('click', () => {
            this.focus(this.map.getSource('route')._data)
        } )
        // Fly button
        var buttonFly = document.createElement('button')
        buttonFly.className = 'map-controller-block mp-button mp-button-small'
        buttonFly.id = 'buttonFly'
        buttonFly.innerText = 'Fly'
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

    async load (element, style, center = this.defaultCenter) {
        return new Promise ((resolve, reject) => {
            this.$map = element
            this.map = new mapboxgl.Map ( {
                container: element,
                center,
                zoom: this.defaultZoom,
                style: style,
                preserveDrawingBuffer: true,
                accessToken: 'pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdyNGYxbjAxd2MzbG12M3V1bjM1MGIifQ.bFRgCmK9_kkfZSd_skNF1g',
                attributionControl: false
            } )

            // Add basic controls
            this.map.addControl(new mapboxgl.NavigationControl())
            this.map.addControl(
                new mapboxgl.AttributionControl( {
                    customAttribution: '© CyclingFriends',
                    compact: true
                } )
            )

            // Style and data
            this.map.once('load', () => {
                this.loaded = true
                resolve (this.map)
                this.styleSeason()
                this.loadTerrain()
                this.loadImages()
            } )
            
            this.map.on('contextmenu', async () => {
                var limits = {
                    min_zoom: 10,
                    max_zoom: 12
                };
                var routeData = await this.getRouteData()
                var tile = cover.tiles(routeData.geometry, limits)
                console.log(tile)
                this.generateProfile()
            } )

            this.centerOnUserLocation()
        } )
    }
    
    addSources () {
        // Toilets
        this.map.addSource('toilets', {
            'type': 'geojson',
            'data': '/map/sources/compressed_sources/toilets.geojson',
            'generateId': true
        } )
        // Drinking
        this.map.addSource('drinking-water', {
            'type': 'geojson',
            'data': '/map/sources/compressed_sources/drinking.geojson',
            'generateId': true
        } )
        // Vending machines
        this.map.addSource('vending-machine-drinks', {
            'type': 'geojson',
            'data': '/map/sources/compressed_sources/vending-machine-drinks.geojson',
            'generateId': true
        } )
        // Onsens
        this.map.addSource('onsens', {
            'type': 'geojson',
            'data': '/map/sources/compressed_sources/onsens.geojson',
            'generateId': true
        } )
        // Konbinis
        this.map.addSource('konbinis', {
            'type': 'geojson',
            'data': '/map/sources/compressed_sources/konbinis.geojson',
            'generateId': true
        } )
        // Rindos
        this.map.addSource('rindos', {
            'type': 'vector',
            'url': 'mapbox://sisbos.c9kguqvi',
            'generateId': true
        } )
        // Cycling
        this.map.addSource('cycling', {
            'type': 'vector',
            'url': 'mapbox://sisbos.9to38xqk',
            'generateId': true
        } )
        // No bicycle
        this.map.addSource('no-bicycle', {
            'type': 'vector',
            'url': 'mapbox://sisbos.5qhdcfue',
            'generateId': true
        } )
    }

    addLayers () {
        // Amenities
        this.addAmenityLayers()
        // Konbinis
        this.addKonbiniLayers()
        // Onsens
        this.map.addLayer( {
            'id': 'onsens',
            'type': 'symbol',
            'source': 'onsens',
            'minzoom': 13,
            'layout': {
                'icon-image': '_icon-onsen',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    13,
                    1,
                    20,
                    2
                ]
            },
            'paint': {
                'icon-opacity': [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter':
                [
                    "match",
                    ["get", "bath:type"],
                    ["foot_bath"],
                    false,
                    true
                ],
        } )
        this.map.addLayer( {
            'id': 'footbaths',
            'type': 'symbol',
            'source': 'onsens',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-footbath'
            },
            'paint': {
                'icon-opacity': [
                    'case',
                    ['boolean', ['feature-state', 'hover'], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                ["get", "bath:type"],
                ["foot_bath"],
                true,
                false
            ],
        } )
        
        this.addRindoLayers()
        this.addCyclingLayers()
    }

    addAmenityLayers () {
        // Toilets
        this.map.addLayer( {
            'id': 'toilets',
            'type': 'symbol',
            'source': 'toilets',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-toilets',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    12,
                    0.6,
                    22,
                    2
                ]
            },
            'paint': {
                'icon-opacity': [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
        } )
        // Drinks
        this.map.addLayer( {
            'id': 'drinking-water',
            'type': 'symbol',
            'source': 'drinking-water',
            'minzoom': 12.5,
            'layout': {
                'icon-image': '_icon-water',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    12.5,
                    0.6,
                    22.5,
                    2
                ]
            },
            'paint': {
                'icon-opacity': [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                ["get", "amenity"],
                ["drinking_water"],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'vending-machine-drinks',
            'type': 'symbol',
            'source': 'vending-machine-drinks',
            'minzoom': 13.5,
            'layout': {
                'icon-image': '_icon-vending-machine',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    13.5,
                    0.6,
                    22,
                    2
                ]
            },
            'paint': {
                'icon-opacity': [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                ["get", "amenity"],
                ["vending_machine"],
                true,
                false
            ]
        } )
    }
    
    hideAmenityLayers () {
        var amenityLayerNames = ['toilets', 'drinking-water', 'vending-machine-drinks']
        amenityLayerNames.forEach( (layerName) => this.map.removeLayer(layerName))
    }

    addKonbiniLayers () {
        
        this.map.addLayer( {
            'id': 'seven-eleven',
            'type': 'symbol',
            'source': 'konbinis',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-seven-eleven',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    12.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                "icon-opacity": [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                [
                    "slice",
                    ["get", "name"],
                    0,
                    3
                ],
                [
                    "any",
                    "セブン",
                    "sev",
                    "7-E"
                ],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'family-mart',
            'type': 'symbol',
            'source': 'konbinis',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-family-mart',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    12.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                "icon-opacity": [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter': 
                ["all",
                    ["match",
                        ["slice",
                            ["get", "name"],
                            0,
                            4
                        ],
                        ["any",
                            "ファミリ",
                            "Fami",
                            "Fimi",
                            "サークル",
                            "Circ",
                            "7-E"
                        ],
                        true,
                        false
                    ],
                    ["match",
                        ["get", "name"],
                        ["any",
                            "ロッジ",
                            "lodge"
                        ],
                        false,
                        true
                    ]
                ]
        } )
        this.map.addLayer( {
            'id': 'mb-family-mart',
            'type': 'symbol',
            'source': 'composite',
            'source-layer': 'poi_label',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-family-mart',
                'icon-size': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    12.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                'icon-opacity': [
                    'case',
                    ['boolean', ['feature-state', 'hover'], false],
                    0.5,
                    1
                ]
            },
            'filter': 
                ['all',
                    ["match",
                        ["slice",
                            ["get", "name"],
                            0,
                            4
                        ],
                        ["any",
                            "ファミリ",
                            "Fami",
                            "Fimi",
                            "サークル",
                            "Circ",
                            "7-E"
                        ],
                        true,
                        false
                    ],
                    ["match",
                        ["get", "name"],
                        ['any',
                            "ロッジ",
                            "lodge"
                        ],
                        false,
                        true
                    ]
                ]
        } )
        this.map.addLayer( {
            'id': 'lawson',
            'type': 'symbol',
            'source': 'konbinis',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-lawson',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    12.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                'icon-opacity': [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                [
                    "slice",
                    ["get", "name"],
                    0,
                    4
                ],
                [
                    "any",
                    "ローソン",
                    "Laws",
                    "LAWS"
                ],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'mini-stop',
            'type': 'symbol',
            'source': 'konbinis',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-mini-stop',
                'icon-size': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    12.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                'icon-opacity': [
                    'case',
                    ['boolean', ['feature-state', 'hover'], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                [
                    "slice",
                    ["get", "name"],
                    0,
                    4
                ],
                [
                    "any",
                    "ミニスト",
                    "Mini",
                    "MINI"
                ],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'daily-yamazaki',
            'type': 'symbol',
            'source': 'konbinis',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-daily-yamazaki',
                'icon-size': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    12.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                'icon-opacity': [
                    'case',
                    ['boolean', ['feature-state', 'hover'], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                [
                    "slice",
                    ["get", "name"],
                    0,
                    4
                ],
                [
                    "any",
                    "Dail",
                    "DAIL",
                    "デイリー",
                    "Yama",
                    "ヤマザキ",
                    "YAMA",
                    "ニューヤ"
                ],
                true,
                false
            ]
        } )
    
        // Michi no Eki
        this.map.addLayer( {
            'id': 'michi-no-eki',
            'type': 'symbol',
            'source': "composite",
            'source-layer': "poi_label",
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-michi-no-eki',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    11.5,
                    0.8,
                    20,
                    3
                ]
            },
            'paint': {
                'icon-opacity': [
                    "case",
                    ["boolean", ["feature-state", "hover"], false],
                    0.5,
                    1
                ]
            },
            'filter': [
                "match",
                    [
                        "slice",
                        ["get", "name"],
                        0,
                        3
                    ],
                    [
                        "any",
                        "道の駅",
                        "mic",
                        "Mic",
                        "MIC"
                    ],
                true,
                false
            ]
        } )
    }

    hideKonbiniLayers () {
        var konbiniLayerNames = ['seven-eleven', 'family-mart', 'mb-family-mart', 'lawson', 'mini-stop', 'daily-yamazaki', 'michi-no-eki']
        konbiniLayerNames.forEach( (layerName) => this.map.removeLayer(layerName))
    }

    addCyclingLayers () {

    }

    // Rindos
    addRindoLayers () {
        this.map.addLayer( {
            'id': 'rindos-case',
            'type': 'line',
            'source': 'rindos',
            'source-layer': 'rindos-b4dkbp',
            'minzoom': 11,
            'paint': {
                'line-opacity': 0,
                'line-width': [
                    'interpolate',
                    ['exponential', 1.5],
                    ['zoom'],
                    11,
                    5,
                    20,
                    15
                ]
            }
        } )
        this.map.addLayer( {
            'id': 'rindos',
            'type': 'line',
            'source': 'rindos',
            'source-layer': 'rindos-b4dkbp',
            'minzoom': 11,
            'paint': {
                'line-color': '#fff',
                'line-opacity': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    11,
                    0,
                    12,
                    1
                ],
                'line-width': [
                    'interpolate',
                    ['exponential', 1.5],
                    ['zoom'],
                    11,
                    1,
                    20,
                    6
                ],
                'line-dasharray': [4, 1, 2, 1]
            }
        } )
        this.map.addLayer( {
            'id': 'rindos-cap',
            'type': 'line',
            'source': 'rindos',
            'source-layer': 'rindos-b4dkbp',
            'minzoom': 11,
            'paint': {
                'line-color': '#fff',
                'line-width': 5,
                'line-color': '#ff5555'
            },
            'filter': ['in', 'name', 'default']
        } )
        this.map.addLayer( {
            'id': 'rindo-labels',
            'type': 'symbol',
            'source': 'rindos',
            'source-layer': 'rindos-b4dkbp',
            'minzoom': 9,
            'layout': {
                'text-field': ["to-string", ["get", "name"]],
                'text-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    10,
                    10,
                    20,
                    20
                ],
                'text-line-height': 1.2,
                'symbol-placement': 'line',
                'symbol-spacing': 30,
                'text-max-angle': 30,
                //'text-padding': 2
            },
            'paint': {
                'text-color': "#000",
                'text-halo-color': "#d6d6d6",
                'text-halo-width': 1,
                'text-halo-blur': 2
            }
        } )
    }

    addCyclingLayers () {
        
        // Cycling
        this.map.addLayer( {
            'id': 'cycle-lane',
            'type': 'line',
            'source': 'cycling',
            'source-layer': 'cycling-dpgl4s',
            'minzoom': 7,
            'paint': {
                'line-color': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    "#6198ff",
                    22,
                    "#0d53d3"
                ],
                'line-width': [
                    "interpolate",
                    ["exponential", 1],
                    ["zoom"],
                    9,
                    1,
                    22,
                    5
                ],
                'line-dasharray': [1, 1]
            },
            'layout': {
                'line-cap': 'butt',
                'line-miter-limit': 2
            },
            'filter': [
                "any", 
                ["match",
                    ["get", "bicycle"],
                    ["lane"],
                    true,
                    false
                ],
                ["match",
                    ["get", "cycleway"],
                    ["lane"],
                    true,
                    false
                ]
            ]
        } )
        this.map.addLayer( {
            'id': 'walk-path',
            'type': 'line',
            'source': 'cycling',
            'source-layer': 'cycling-dpgl4s',
            'minzoom': 7,
            'paint': {
                'line-color': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    "hsl(219, 34%, 79%)",
                    22,
                    "hsl(221, 63%, 62%)"
                ],
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    1,
                    22,
                    5
                ]
            },
            'filter': [
                "all",
                [
                    "match",
                    ["get", "highway"],
                    ["cycleway"],
                    false,
                    true
                ],
                [
                    "match",
                    ["get", "cycleway"],
                    ["lane"],
                    false,
                    true
                ],
                [
                    "match",
                    ["get", "bicycle"],
                    ["lane"],
                    false,
                    true
                ]
            ]
        } )
        this.map.addLayer( {
            'id': 'walk-path-case',
            'type': 'line',
            'source': 'cycling',
            'source-layer': 'cycling-dpgl4s',
            'minzoom': 7,
            'paint': {
                'line-color': '#f2f0ee',
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    1,
                    22,
                    2
                ],
                'line-gap-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    1,
                    22,
                    5
                ]
            },
            'filter': [
                "all",
                [
                    "match",
                    ["get", "highway"],
                    ["cycleway"],
                    false,
                    true
                ],
                [
                    "match",
                    ["get", "cycleway"],
                    ["lane"],
                    false,
                    true
                ],
                [
                    "match",
                    ["get", "bicycle"],
                    ["lane"],
                    false,
                    true
                ]
            ]
        } )
        this.map.addLayer( {
            'id': 'cycle-path',
            'type': 'line',
            'source': 'cycling',
            'source-layer': 'cycling-dpgl4s',
            'minzoom': 7,
            'paint': {
                'line-color': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    "#6198ff",
                    22,
                    "#0d53d3"
                ],
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    1,
                    22,
                    5
                ]
            },
            'filter': [
                "match",
                ["get", "highway"],
                ["cycleway"],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'cycle-path-case',
            'type': 'line',
            'source': 'cycling',
            'source-layer': 'cycling-dpgl4s',
            'minzoom': 7,
            'paint': {
                'line-color': "#f2f0ee",
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    0.5,
                    22,
                    2.5
                ],
                'line-gap-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    9,
                    1,
                    22,
                    2
                ]
            },
            'filter': [
                "match",
                ["get", "highway"],
                ["cycleway"],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'cycle-path-cap',
            'type': 'line',
            'source': 'cycling',
            'source-layer': 'cycling-dpgl4s',
            'minzoom': 11,
            'paint': {
                'line-color': '#fff',
                'line-width': 5,
                'line-color': '#ff5555'
            },
            'filter': ['in', 'name', 'default']
        } )

        // No bicycle
        this.map.addLayer( {
            'id': 'no-bicycle-motorways',
            'type': 'line',
            'source': 'no-bicycle',
            'source-layer': 'no-bicycle-2drz45',
            'minzoom': 11,
            'paint': {
                'line-color': "#c8ebc6",
                'line-width': [
                    "interpolate",
                    ["exponential", 1.5],
                    ["zoom"],
                    5,
                    0.75,
                    18,
                    32
                ]
            },
            'filter': [
                "match",
                ["get", "highway"],
                ["motorway"],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'no-bicycle-case',
            'type': 'line',
            'source': 'no-bicycle',
            'source-layer': 'no-bicycle-2drz45',
            'minzoom': 11,
            'paint': {
                'line-color': "#fff",
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    8,
                    1,
                    22,
                    5
                ]
            },
            'filter': [
                "match",
                ["get", "highway"],
                ["motorway"],
                false,
                true
            ]
        } )
        this.map.addLayer( {
            'id': 'no-bicycle',
            'type': 'line',
            'source': 'no-bicycle',
            'source-layer': 'no-bicycle-2drz45',
            'minzoom': 11,
            'paint': {
                'line-color': "#99ffca",
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    8,
                    1,
                    22,
                    5
                ],
                'line-dasharray': [2, 2]
            },
            'filter': [
                "match",
                ["get", "highway"],
                ["motorway"],
                false,
                true
            ]
        } )
        this.map.addLayer( {
            'id': 'no-bicycle-rindos-cap',
            'type': 'line',
            'source': 'rindos',
            'source-layer': 'rindos-b4dkbp',
            'minzoom': 11,
            'paint': {
                'line-color': "#fff",
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    8,
                    1,
                    22,
                    5
                ]
            },
            'filter': [
                "match",
                ["get", "bicycle"],
                ["no"],
                true,
                false
            ]
        } )
        this.map.addLayer( {
            'id': 'no-bicycle-rindos',
            'type': 'line',
            'source': 'rindos',
            'source-layer': 'rindos-b4dkbp',
            'minzoom': 11,
            'paint': {
                'line-color': "#99ffca",
                'line-width': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    8,
                    1,
                    22,
                    5
                ],
                'line-dasharray': [2, 2]
            },
            'filter': [
                "match",
                ["get", "bicycle"],
                ["no"],
                true,
                false
            ]
        } )
    }

    styleSeason () {

        // Define seasonnal colors
        var winter = [
            "match",
            ["get", "class"],
            "snow",
            "hsl(35, 11%, 100%)",
            ["wood"],
            "hsl(69, 43%, 69%)",
            ["grass"],
            "hsl(41, 48%, 88%)",
            ["scrub"],
            "hsl(77, 43%, 68%)",
            "hsl(25, 19%, 80%)"
        ]
        var spring = [
            "match",
            ["get", "class"],
            "snow",
            "hsl(35, 11%, 100%)",
            ["wood"],
            "hsl(109, 100%, 51%)",
            ["grass"],
            "hsl(103, 98%, 74%)",
            ["scrub"],
            "hsl(360, 100%, 86%)",
            "hsl(360, 0%, 100%)"
        ]
        var summer = [
            "match",
            ["get", "class"],
            "snow",
            "hsl(35, 11%, 100%)",
            ["wood"],
            "hsl(99, 100%, 26%)",
            ["grass"],
            "hsl(88, 100%, 71%)",
            ["scrub"],
            "hsl(106, 100%, 57%)",
            "hsl(25, 19%, 80%)"
        ]
        var fall = [
            "match",
            ["get", "class"],
            "snow",
            "hsl(35, 11%, 100%)",
            ["wood"],
            "hsl(20, 78%, 73%)",
            ["grass"],
            "hsl(90, 66%, 70%)",
            ["scrub"],
            "hsl(88, 51%, 55%)",
            "hsl(25, 19%, 80%)"
        ]

        // Define season
        if (this.season == 'winter') var colors = winter
        else if (this.season == 'spring') var colors = spring
        else if (this.season == 'summer') var colors = summer
        else if (this.season == 'fall') var colors = fall

        this.map.addLayer( {
            'id': 'landcover-season',
            'type': 'fill',
            'source': 'composite',
            'source-layer': 'landcover',
            'minzoom': 0,
            'maxzoom': 22,
            'paint': {
                'fill-color': colors,
                'fill-opacity': [
                    "interpolate",
                    ["exponential", 1.5],
                    ["zoom"],
                    2,
                    0.3,
                    12,
                    0.15,
                    16,
                    0
                ]
            }
        }, 'landcover-custom')

        ///if (this.season != 'undefined') this.map.setPaintProperty('landcover-custom', 'fill-color', colors)
    }

    // Add media
    async loadImages () {
        if (!this.map.hasImage('leader-line-white')) {
            this.map.loadImage('/map/media/leader-line-white.png', (error, image) => {
                if (error) throw error
                this.map.addImage('leader-line-white', image)
            } )
        }
        var amenityIcons = ['toilets', 'water', 'vending-machine', 'seven-eleven', 'family-mart', 'lawson', 'mini-stop', 'daily-yamazaki', 'michi-no-eki', 'onsen', 'footbath']
        amenityIcons.forEach( async (amenityIcon) => {
            var imageName = '_icon-' + amenityIcon
            if (!this.map.hasImage(imageName)) {
                this.map.loadImage('/map/media/' + imageName + '.png', (error, image) => {
                    if (error) throw error
                    this.map.addImage(imageName, image)
                } )
            }
        } )
    }

    // Load elevation data
    loadTerrain () {
        if (!this.map.getSource('mapbox-dem')) {
            this.map.addSource('mapbox-dem', {
                'type': 'raster-dem',
                'url': 'mapbox://mapbox.terrain-rgb',
                'tileSize': 512,
                'maxzoom': 14
            })
        }
    }

    centerOnUserLocation () {
        return
    }

    async generateProfile (options = {force: false, time: false}) {
        
        const route = this.map.getSource('route')

        // If a route is displayed on the map
        if (route) {

            // Prepare profile data
            /*if (!this.profileData)*/ this.profileData = await this.getProfileData(route._data, {remote: true})
            
            // Draw profile inside elevationProfile element

            // Prepare profile settings
            const ctx = document.getElementById('elevationProfile').getContext('2d')
            const downtwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y + 2 ? value : undefined
            const flat = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 2 ? value : undefined
            const uptwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 6 ? value : undefined
            const upsix = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 10 ? value : undefined
            const upten = (ctx, value) => ctx.p0.parsed.y > 0 ? value : undefined
            console.log(this.profileData)
            const data = {
                labels: this.profileData.labels,
                datasets: [ {
                    data: this.profileData.pointData,
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
                    lingrad.addColorStop(0, '#f9f9f9');
                    lingrad.addColorStop(0.5, '#fff');
                    ctx.fillStyle = lingrad
                    ctx.fillRect(0, 0, chart.width, chart.height)
                    ctx.restore()
                }
            }
            const displayPois = {
                id: 'displayPois',
                afterRender: (chart) => {
                    const ctx = chart.canvas.getContext('2d')
                    const routeData = route._data
                    const routeDistance = turf.length(routeData)
                    var drawPoi = async (poi, type) => {
                        // Get X position
                        const pointDistance = poi.distance
                        var roughPositionProportion = pointDistance / routeDistance * 100
                        var roughPositionPixel = roughPositionProportion * (chart.scales.x._maxLength - chart.scales.x.left - chart.scales.x.paddingRight) / 100
                        poi.position = roughPositionPixel + chart.scales.x.left
                        // Get Y position
                        const dataX = chart.scales.x.getPixelForValue(pointDistance)
                        const dataY = chart.scales.y.getPixelForValue(this.profileData.averagedPointsElevation[Math.floor(pointDistance * 10)])
                        // Draw a line
                        var cursorLength = 10
                        ctx.strokeStyle = '#d6d6d6'
                        ctx.lineWidth = 1
                        ctx.beginPath()
                        ctx.moveTo(poi.position, dataY)
                        ctx.lineTo(poi.position, dataY - cursorLength)
                        ctx.stroke()
                        ctx.closePath()

                        // Format icon
                        if (type == 'mkpoint') {
                            poi.number = poi.id
                            var img = document.querySelector('#' + type + poi.number).querySelector('img')
                        } else if (type == 'rideCheckpoint') var img = document.querySelector('#' + 'checkpointPoiIcon' + poi.number)
                        else if (type == 'activityCheckpoint') {
                            var svgElement = document.querySelector('#' + 'checkpoint' + poi.number + ' svg')
                            var img = new Image()
                            img.src = 'https://api.iconify.design/' + svgElement.dataset.icon.replace(':', '/') + '.svg'
                            img.height = 24
                            img.width = 24
                        }
                        // Prepare profile drawing variables
                        var width  = 15
                        var height = 15
                        const positionX = poi.position - width / 2
                        const positionY = dataY - cursorLength - height
                        // If first loading, wait for img to load if not loaded yet, else use it directly
                        if (!document.querySelector('canvas#offscreenCanvas' + poi.number)) {
                            if (img.complete) drawOnCanvas(img)
                            else img.addEventListener('load', () => drawOnCanvas(img))

                            function drawOnCanvas (img) {
                                if (img.classList.contains('admin-marker')) {
                                    ctx.strokeStyle = 'yellow'
                                    ctx.lineWidth = 3
                                }
                                if (img.classList.contains('selected-marker')) {
                                    ctx.strokeStyle = '#ff5555'
                                    ctx.lineWidth = 3
                                }
        
                                var abstract = {}
                                abstract.offscreenCanvas = document.createElement("canvas")
                                abstract.offscreenCanvas.width = width
                                abstract.offscreenCanvas.height = height
                                abstract.offscreenContext = abstract.offscreenCanvas.getContext("2d")
                                const ctx2 = abstract.offscreenContext
                                ctx2.drawImage(img, 0, 0, width, height)
                                ctx2.globalCompositeOperation = 'destination-atop'
                                ctx2.arc(0 + width/2, 0 + height/2, width/2, 0, Math.PI * 2)
                                ctx2.closePath()
                                ctx2.fillStyle = "#fff"
                                ctx2.fill()
                                // Keep offscreenCanvas 'in cache' for next profile generating 
                                abstract.offscreenCanvas.style.display = 'none'
                                abstract.offscreenCanvas.id = 'offscreenCanvas' + poi.number
                                document.body.appendChild(abstract.offscreenCanvas)
        
                                // Draw icon
                                ctx.drawImage(abstract.offscreenCanvas, positionX, positionY)
                                ctx.beginPath()
                                ctx.arc(positionX + width/2, positionY + height/2, width/2, 0, Math.PI * 2)
                                ctx.closePath()
                                ctx.stroke()
                            }
                        // If img has already been loaded, direcly use it for preventing unnecessary loading time
                        } else {
                            var offscreenCanvas = document.querySelector('canvas#offscreenCanvas' + poi.number)
                            // Draw icon on profile
                            ctx.drawImage(offscreenCanvas, positionX, positionY)
                            ctx.beginPath()
                            ctx.arc(positionX + width/2, positionY + height/2, width/2, 0, Math.PI * 2)
                            ctx.closePath()
                            ctx.stroke()
                        }
                    }
                    // For mkpoints
                    if (this.mkpoints) this.mkpoints.forEach( (mkpoint) => {
                            if (mkpoint.on_route && document.querySelector('#boxShowMkpoints').checked) drawPoi(mkpoint, 'mkpoint')
                        } )
                    // For ride checkpoints
                    if (this.ride) {
                        this.ride.checkpoints.forEach( (checkpoint) => {
                            drawPoi(checkpoint, 'rideCheckpoint')
                        } )
                    }
                    // For activity checkpoints
                    if (this.activityId) {
                        this.data.checkpoints.forEach( (checkpoint) => {
                            drawPoi(checkpoint, 'activityCheckpoint')
                        } )
                    }
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
                        const altitude     = this.profileData.pointsElevation[distance * 10]
                        // Slope
                        if (this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
                            var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - this.profileData.averagedPointsElevation[Math.floor(distance * 10)]
                        } else { // Only calculate on previous 100m for the last index (because no next index)
                            var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10)] - this.profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
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
                            this.drawTooltip(this.map.getSource('route')._data, routePoint.geometry.coordinates[0], routePoint.geometry.coordinates[1], e.x, false, {backgroundColor: '#ffffff'})
                            // Highlight corresponding mkpoint data
                            if (this.mkpoints && (!document.querySelector('#boxShowMkpoints') || document.querySelector('#boxShowMkpoints').checked)) {
                                this.mkpoints.forEach( (mkpoint) => {
                                    if (document.getElementById(mkpoint.id) && mkpoint.distance < (distance + 1) && mkpoint.distance > (distance - 1)) {
                                        // Highlight preview image
                                        document.getElementById(mkpoint.id).querySelector('img').classList.add('admin-marker')
                                        // Highlight marker
                                        document.querySelector('#mkpoint' + mkpoint.id).querySelector('img').classList.add('admin-marker')
                                    } else if (document.getElementById(mkpoint.id) && mkpoint.on_route == true) {
                                        document.getElementById(mkpoint.id).querySelector('img').classList.remove('admin-marker')
                                        document.querySelector('#mkpoint' + mkpoint.id).querySelector('img').classList.remove('admin-marker')
                                    }
                                } )
                            }
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
                plugins: [backgroundColor, cursorOnHover, displayPois]
            }

            // Reset canvas
            if (this.elevationProfile) {
                this.elevationProfile.destroy()
            }
            // Bound chart to canvas
            this.elevationProfile = new Chart(ctx, chartSettings)
        }
    }
    
    // Remove 'selected-marker' class from all selected markers
    unselect () {
        var openedPopupId = 'none' // Prevent auto unselecting if popup opening and closing timing is wrong. If below condition is not included, style will not be switched in case of clicking another marker.
        // Remove selected class from map marker elements
        this.map._markers.forEach( (marker) => {
            if (!marker.getPopup().isOpen()) {
                marker.getElement().classList.remove('selected-marker')
                if (marker.getElement().querySelector('img')) marker.getElement().querySelector('img').classList.remove('selected-marker')
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
        this.generateProfile()
    }

    closeControlsOnMobile () {
        this.$map.querySelectorAll('.map-controller-block').forEach( (block) => {
            if (block.querySelector('.map-controller-label') && block.querySelector('.map-controller-label').classList.contains('up')) {
                block.querySelector('.map-controller-label').classList.remove('up')
                block.querySelectorAll('.map-controller-line').forEach( (line) => {
                    line.classList.add('hide-on-mobiles')
                } )
            }
        } )
    }

    addRouteLayer (geojson) {
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
                'line-width': this.routeWidth,
                'line-opacity': 1,
            }
        } )
        if (!this.directionsMode) this.prepareTooltip() // Only prepare tooltip if not on route build mode
        // Prevent profile point of remaining on the map after leaving profile canvas area
        this.map.on('mousemove', () => {
            if (this.map.getLayer('profilePoint')) {
                this.map.removeLayer('profilePoint')
                this.map.removeSource('profilePoint')
            }
        } )
    }

    updateDistanceMarkersListener = this.updateDistanceMarkers.bind(this)
    updateDistanceMarkers () {
        if (this.map.getSource('route')) {
            var boxShowDistanceMarkers = document.querySelector('#boxShowDistanceMarkers')
            if (boxShowDistanceMarkers && boxShowDistanceMarkers.checked || !boxShowDistanceMarkers) {
                const routeData = this.map.getSource('route')._data
                const distance  = turf.length(routeData)
                // Define basis according to distance and zoom level
                var basis = defineDistanceMarkersBasis(distance, this.map.getZoom())
                // Set distance markers data
                const distanceMarkers = {
                    type: 'FeatureCollection',
                    features: []
                }
                for (let cursor = basis; cursor < distance; cursor += basis) {
                    var distanceMarkerPoint = turf.along(routeData, cursor, {units: 'kilometers'})
                    distanceMarkers.features.push( {
                        type: 'Feature',
                        properties: {
                            label: cursor,
                            distance: cursor
                        },
                        geometry: distanceMarkerPoint.geometry
                    } )
                }
                // Set distance markers color property
                if (this.routeColor == 'blue') var color = 'blue'
                else var color = 'black'
                // Add to map
                if (this.map.getSource('distanceMarkers')) {
                    this.hideDistanceMarkers()
                }
                this.map.addSource('distanceMarkers', {
                    type: 'geojson',
                    data: distanceMarkers
                })
                this.map.addLayer( {
                    id: 'distanceMarkers',
                    type: 'symbol',
                    source: 'distanceMarkers',
                    layout: {
                        'text-field': ['get', 'label'],
                        'text-anchor': 'bottom',
                        'text-radial-offset': 1,
                        'text-justify': 'auto',
                        'icon-image': 'leader-line-white',
                        'icon-anchor': 'bottom',
                        'symbol-sort-key': 1
                    },
                    paint: {
                        'text-color': color,
                        'text-halo-color': 'white',
                        'text-halo-width': 1
                    }
                } )
                // Update on zoom
                this.map.on('zoomend', this.updateDistanceMarkersListener)
            } else {
                this.hideDistanceMarkers()
            }
            
            function defineDistanceMarkersBasis (distance, zoomLevel) {
                if (zoomLevel < 6) {
                    return 100
                }
                if (distance <= 10) {
                    if (zoomLevel > 11) {
                        return 1
                    } else if (zoomLevel > 6) {
                        return 2
                    } else {
                        return 5
                    }
                } else if (distance <= 50 && distance > 10) {
                    if (zoomLevel > 14) {
                        return 2
                    } else if (zoomLevel > 11) {
                        return 5
                    } else {
                        return 10
                    }
                } else if (distance > 50) {
                    if (zoomLevel > 15) {
                        return 2
                    } else if (zoomLevel > 13) {
                        return 5
                    } else if (zoomLevel > 11) {
                        return 10
                    } else {
                        return 20
                    }
                }
            }
        }
    }

    scaleMarkerAccordingToZoom (element) {
        console.log(element)
        var zoom = this.map.getZoom()
        var size = (zoom * 3 - 10) - 5
        if (size < 15) {
            size = 15
        }
        console.log(size)
        element.style.height = size + 'px'
        element.style.width = size + 'px'
        element.style.border = size/15 + 'px solid white'
    }

    hideDistanceMarkers () {
        if (this.map.getSource('distanceMarkers')) {
            this.map.removeLayer('distanceMarkers')
            this.map.removeSource('distanceMarkers')
        }
        this.map.off('zoomend', this.updateDistanceMarkersListener)
    }

    displayStartGoalMarkers (routeData) {
        if (!document.querySelector('#boxShowDistanceMarkers') || document.querySelector('#boxShowDistanceMarkers').checked) {
            const routeCoordinates = routeData.geometry.coordinates
            var startCoordinates = routeCoordinates[0]
            var goalCoordinates = routeCoordinates[routeCoordinates.length - 1]

            // If start and goal are located more than 200m to each other, draw it separately
            if (turf.length(turf.lineString([startCoordinates, goalCoordinates])) > 0.2) {
                var geojson = {
                    type: 'FeatureCollection',
                    features: [ 
                        {
                            type: 'Feature',
                            properties: {
                                label: 'START',
                                color: 'white',
                                halocolor: '#00e06e'
                            },
                            geometry: {
                                type: 'Point',
                                coordinates: startCoordinates
                            }
                        },
                        {
                            type: 'Feature',
                            properties: {
                                label: 'GOAL',
                                color: 'white',
                                halocolor: '#ff5555'
                            },
                            geometry: {
                                type: 'Point',
                                coordinates: goalCoordinates
                            }
                        }
                    ]
                }
            // If start and goal are located less than 50m to each other, draw it together
            } else {
                var geojson = {
                    type: 'FeatureCollection',
                    features: [ 
                        {
                            type: 'Feature',
                            properties: {
                                label: 'START/GOAL',
                                color: 'white',
                                halocolor: '#ff5555'
                            },
                            geometry: {
                                type: 'Point',
                                coordinates: startCoordinates
                            }
                        }
                    ]
                }
            }
            if (!this.map.getSource('startGoal')) {
                this.map.addSource('startGoal', {
                    type: 'geojson',
                    data: geojson
                } )
            }
            this.map.addLayer( {
                id: 'startGoal',
                type: 'symbol',
                source: 'startGoal',
                layout: {
                    'text-field': ['get', 'label'],
                    'text-anchor': 'bottom',
                    'text-radial-offset': 1,
                    'text-justify': 'auto',
                    'icon-image': 'leader-line-white',
                    'icon-anchor': 'bottom',
                    'symbol-sort-key': 0
                },
                paint: {
                    'text-color': ['get', 'color'],
                    'text-halo-color': ['get', 'halocolor'],
                    'text-halo-width': 2
                }
            } )
        }
    }

    hideStartGoalMarkers () {
        if (this.map.getSource('startGoal')) {
            this.map.removeLayer('startGoal')
            this.map.removeSource('startGoal')
        }
    }

    async loadCloseMkpoints (range, options = {displayOnMap: true, generateProfile: true, getFileBlob: true}) {
        return new Promise ( async (resolve, reject) => {

            // Display close mkpoints inside the map
            ajaxGetRequest ('/api/map.php' + "?display-mkpoints=" + this.routeId, async (response) => {

                var mkpoints = await this.getClosestMkpoints(response, range)

                // Display on map
                if (options.displayOnMap) this.addMkpoints(mkpoints)

                // Update mkpoints cursors on profile
                if (options.generateProfile) this.generateProfile()
                
                // Display thumbnails
                // Get mkpoints on route number
                mkpoints.forEach( (mkpoint) => {
                    if (mkpoint.on_route) this.mkpointsOnRouteNumber++
                } )

                // For each mkpoint
                if (options.getFileBlob) {
                    for (let i = 0; i < mkpoints.length; i++) {
                        // Get images if needed
                        mkpoints[i].file_blob = await this.getFileBlob(mkpoints[i])
                    }
                }
                resolve(mkpoints)
            } )
        } )
    }

    async getFileBlob (mkpoint) {
        return new Promise ( async (resolve, reject) => {
            ajaxGetRequest ('/api/map.php' + "?mkpoint-closest-photo=" + mkpoint.id, async (response) => {
                resolve(response.file_blob)
            } )
        } )
    }
    
    getClosestMkpoints (mkpoints, range) {
        return new Promise ( async (resolve, reject) => {

            const remotenessTolerance = 0.1
            var mkpointsInRange = []
            var closeMkpoints = []

            // Get route
            const routeData = await this.getRouteData()

            // Build a simplified line for rough filtering
            var coreLine = turf.simplify(routeData, {tolerance: 0.02, highQuality: false, mutate: false})
            mkpoints.forEach( (mkpoint) => {
                var point = turf.point([mkpoint.lng, mkpoint.lat])
                var nearestLinePoint = turf.nearestPointOnLine(coreLine, point)
                var roughRemoteness = nearestLinePoint.properties.dist
                if (range < 3) var roughRange = range * 8 // Define range from the coreline where to keep mkpoints according range value to prevent too small range
                else var roughRange = range * 3
                if (roughRemoteness < roughRange) {
                    mkpointsInRange.push(mkpoint)
                }
            } )

            // Get route remoteness
            mkpointsInRange.forEach( (mkpoint) => {
                var point = turf.point([mkpoint.lng, mkpoint.lat])
                var nearestLinePoint = turf.nearestPointOnLine(routeData, point)
                mkpoint.remoteness = nearestLinePoint.properties.dist
                mkpoint.distance = nearestLinePoint.properties.location
                if (mkpoint.remoteness < range) {
                    if (mkpoint.remoteness < remotenessTolerance) mkpoint.on_route = true
                    else mkpoint.on_route = false
                    closeMkpoints.push(mkpoint)
                }
            } )
            // Sort mkpoints by distance
            closeMkpoints.sort( (mkpointA, mkpointB) => mkpointA.distance - mkpointB.distance)

            resolve(closeMkpoints)
        } )
    }

    displaySegment (segment) {

        // Build geojson
        var geojson = {
            type: 'Feature',
            properties: {
                rank: segment.rank,
                name: segment.name,
                specs: {
                    offroad: segment.spec_offroad,
                    rindo: segment.spec_rindo
                },
                tags: {
                    hanami: segment.spec_hanami,
                    kouyou: segment.kouyou,
                    ajisai: segment.ajisai,
                    culture: segment.spec_culture,
                    machinami: segment.spec_machinami,
                    shrines: segment.spec_shrines,
                    teaFields: segment.spec_tea_fields,
                    sea: segment.spec_sea,
                    mountains: segment.spec_mountains,
                    forest: segment.spec_forest,
                },
                tunnels: segment.route.tunnels
            },
            geometry: {
                type: 'LineString',
                coordinates: segment.route.coordinates
            }
        }

        // Add source
        this.map.addSource('segment' + segment.id, {
            type: 'geojson',
            data: geojson
        } )

        // Add segment cap layer
        this.map.addLayer( {
            id: 'segmentCap' + segment.id,
            type: 'line',
            source: 'segment' + segment.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.segmentCapColor,
                'line-width': 2,
                'line-opacity': 0,
                'line-gap-width': 2
            }
        } )

        // Define segment color
        if (segment.rank == 'local') var segmentColor = this.segmentLocalColor
        if (segment.rank == 'regional') var segmentColor = this.segmentRegionalColor
        if (segment.rank == 'national') var segmentColor = this.segmentNationalColor

        // Add segment layer
        this.map.addLayer( {
            id: 'segment' + segment.id,
            type: 'line',
            source: 'segment' + segment.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': segmentColor,
                'line-width': 3,
                'line-opacity': 1
            }
        } )

        // Set animation
        this.map.on('mouseenter', 'segmentCap' + segment.id, () => {
            this.map.getCanvas().style.cursor = 'default'
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 1)
        } )
        this.map.on('mouseleave', 'segmentCap' + segment.id, () => {
            this.map.getCanvas().style.cursor = 'grab'
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 0)
        } )
    }

    getFittingSegments () {

        return new Promise ( async (resolve, reject) => {

            // Get segments fitting the route
            ajaxGetRequest ('/api/map.php' + "?display-segments=" + this.routeId, async (segments) => {
                console.log(segments)

                const remotenessTolerance = 0.1
                const range = 2
                var segmentsInRange = []
                var fittingSegments = []

                // Get route
                const routeData = await this.getRouteData()

                // Build a simplified line for rough filtering
                var coreLine = turf.simplify(routeData, {tolerance: 0.02, highQuality: false, mutate: false})
                segments.forEach( (segment) => {
                    var point = turf.point([segment.route.coordinates[0][0], segment.route.coordinates[0][1]])
                    var nearestLinePoint = turf.nearestPointOnLine(coreLine, point)
                    var roughRemoteness = nearestLinePoint.properties.dist
                    if (range < 3) var roughRange = range * 8 // Define range from the coreline where to keep segments according range value to prevent too small range
                    else var roughRange = range * 3
                    if (roughRemoteness < roughRange) {
                        segmentsInRange.push(segment)
                    }
                } )

                // Get route remoteness
                segmentsInRange.forEach( (segment) => {
                    var point = turf.point([segment.route.coordinates[0][0], segment.route.coordinates[0][1]])
                    var nearestLinePoint = turf.nearestPointOnLine(routeData, point)
                    segment.remoteness = nearestLinePoint.properties.dist
                    segment.distance = nearestLinePoint.properties.location
                    if (segment.remoteness < range) {
                        if (segment.remoteness < remotenessTolerance) segment.on_route = true
                        else segment.on_route = false
                        fittingSegments.push(segment)
                    }
                } )
                // Sort segments by distance
                fittingSegments.sort( (segmentA, segmentB) => segmentA.distance - segmentB.distance)

                resolve(fittingSegments)
            } )
        } )
    }

    addMkpoints (mkpoints) {
        mkpoints.forEach( async (mkpoint) => {
            let mkpointPopup = new MkpointPopup(mkpoint)
            var content = mkpointPopup.setPopupContent(mkpoint)

            let element = document.createElement('div')
            let icon = document.createElement('img')
            icon.src = 'data:image/jpeg;base64,' + mkpoint.thumbnail
            icon.classList.add('mkpoint-icon')
            if (mkpoint.on_route === true) icon.style.boxShadow = '0 0 1px 3px ' + this.routeColor
            element.appendChild(icon)
            this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
            var marker = new mapboxgl.Marker ( {
                anchor: 'center',
                color: '#5e203c',
                draggable: false,
                element: element
            } )

            mkpointPopup.popup.setHTML(content)
            mkpointPopup.popup.setMaxWidth("250px")
            mkpointPopup.data = mkpoint
            marker.setPopup(mkpointPopup.popup)
            marker.setLngLat([mkpoint.lng, mkpoint.lat])
            marker.addTo(this.map)
            marker.getElement().id = 'mkpoint' + mkpoint.id
            marker.getElement().className = 'mkpoint-marker'
            marker.getElement().dataset.id = mkpoint.id
            marker.getElement().dataset.user_id = mkpoint.user_id
            mkpointPopup.popup.on('open', () => {
                this.unselect()
                mkpointPopup.select()
                this.generateProfile()
                mkpointPopup.comments()
                mkpointPopup.rating()
                if (content.includes('mkpointAdminPanel')) mkpointPopup.mkpointAdmin()
                if (content.includes('target-button')) mkpointPopup.setTarget()
                if (content.includes('addphoto-button')) mkpointPopup.addPhoto() 
                if (content.includes('round-propic-img')) mkpointPopup.addPropic()
            } )
            mkpointPopup.popup.on('close', () => {
                this.unselect()
            } )
        } )
    }

    getRouteData () {
        return new Promise ( (resolve, reject) => {
            if (this.data && this.data.routeData) {
                resolve(this.data.routeData)
            } else if (this.map.getSource('route')) {
                resolve(this.map.getSource('route')._data)
            } else {
                this.map.once('sourcedata', 'route', (e) => {
                    if (e.isSourceLoaded == true) {
                        resolve(this.map.getSource('route')._data)
                    }
                } )
            }
        } )
    }

    hideMkpoints () {
        let i = 0
        while (i < this.map._markers.length) {
            if (this.map._markers[i]._element.classList.contains('mkpoint-marker')) this.map._markers[i].remove()
            else i++
        }
        if (this.mkpointsMarkerCollection) this.mkpointsMarkerCollection = []
    }

    // Prepare tooltip display
    prepareTooltip () {
        this.map.on('mousemove', 'route', async (e) => {
            // Clear previous tooltip if displayed
            this.clearTooltip()
            // Prepare information to display
            this.drawTooltip(this.map.getSource('route')._data, e.lngLat.lng, e.lngLat.lat, e.point.x, e.point.y)
        } )
        this.map.on('mouseout', 'route', () => {
            // Clear tooltip
            this.clearTooltip()
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
        var profileData = await this.getProfileData(routeData, {remote: false})
        var altitude = profileData.averagedPointsElevation[Math.floor(distance * 10)]

        // Slope
        if (profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - profileData.averagedPointsElevation[Math.floor(distance * 10)]
        } else { // Only calculate on previous 100m for the last index (because no next index)
            var slope = profileData.averagedPointsElevation[Math.floor(distance * 10)] - profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
        }
        /*
        if (!this.profileData) this.profileData = await this.getProfileData(routeData, {remote: false})
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
            Distance : ` + dst1 + `km, ` + dst2 + `km<br>
            Slope : <div class="map-slope">` + slope + `%</div><br>
            Altitude : ` + altitude + `m`
        } else {
            tooltip.innerHTML = `
            Distance : ` + distance + `km<br>
            Slope : <div class="map-slope">` + slope + `%</div><br>
            Altitude : ` + altitude + `m`
        }
        // In case of an activity, add time data
        if (this.activityId) tooltip.innerHTML += '<br>Time : ' + this.getFormattedTimeFromLngLat([lng, lat])
        
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

    // Build profile data
    async getProfileData (routeData, options) { // options : remote = boolean
        const routeDistance = turf.length(routeData)
        const tunnels       = routeData.properties.tunnels
        // Get as many times of 100m distance as it fits inside route distance into an array
        var distances = []
        for (let i = 0; i < routeDistance; i += 0.1) {
            distances.push(i)
        }
        // Get an array of points to check for building route profile
        var profilePoints = getPointsToCheck(routeData, distances)
        if (options) {
            if (options.remote == true) {
                // If profile is displayed, set map bounds to route bounds and wait for map elevation data to load
                if (document.querySelector('.show-profile')) {
                    var routeBounds = CFUtils.defineRouteBounds(routeData.geometry.coordinates)
                    this.map.fitBounds(routeBounds)
                    await this.map.once('idle')
                }
            }
        }
        // Get an array of elevation data for each profile point
        var pointsElevation = []
        for (let i = 0; i < profilePoints.length; i++) {
            var thisPointElevation = Math.floor(this.map.queryTerrainElevation(profilePoints[i].geometry.coordinates, {exaggerated: false}))
            pointsElevation.push(thisPointElevation)
        }
        // Cut tunnels
        var profilePointsCoordinates = []
        profilePoints.forEach( (point) => {
            profilePointsCoordinates.push(point.geometry.coordinates)
        } )
        if (tunnels) {
            tunnels.forEach( (tunnel) => {
                var startClosestSectionCoordinates = CFUtils.closestLocation(tunnel[0], profilePointsCoordinates)
                var startKey = parseInt(getKeyByValue(profilePointsCoordinates, startClosestSectionCoordinates))
                var endClosestSectionCoordinates = CFUtils.closestLocation(tunnel[tunnel.length - 1], profilePointsCoordinates)
                var endKey = parseInt(getKeyByValue(profilePointsCoordinates, endClosestSectionCoordinates))
                if (startKey > endKey) [startKey, endKey] = [endKey, startKey] // Revert variables if found reverse order
                var toSlice = endKey - startKey + 1
                var toInsert = averageElevationFromTips(pointsElevation[startKey], pointsElevation[endKey], toSlice)
                // Replace in array
                toInsert.reverse()
                pointsElevation.splice(startKey, toSlice)
                for (let i = 0; i < toInsert.length; i++) {
                    pointsElevation.splice(startKey, 0, toInsert[i])
                }
            } )
        }
        // Average elevation
        var basis = defineBasis(routeDistance)
        var averagedPointsElevation = averageElevation(pointsElevation, basis)
        // Build labels
        var labels = []
        for (let i = 0; i < (averagedPointsElevation.length); i++) labels.push((i / 10) + ' km')
        // Build points at regular format
        var pointData = []
        for (let i = 0; i < (profilePoints.length); i++) {
            pointData.push({x: distances[i], y: averagedPointsElevation[i]})
        }

        return {
            profilePoints: profilePoints,
            pointsElevation: pointsElevation,
            profilePointsCoordinates: profilePointsCoordinates,
            averagedPointsElevation: averagedPointsElevation,
            pointData: pointData,
            labels: labels
        }

        // Define profile averaging basis on a 100m unit
        function defineBasis (distance) {
            if (distance < 5) {
                return 7
            } else if (distance >= 5 && distance < 30) {
                return 8
            } else if (distance >= 30 && distance < 80) {
                return 9
            } else if (distance >= 80) {
                return 10
            } else {
                return 8
            }
        }

        function getPointsToCheck (lineString, distancesToCheck) {     
            let points = [] 
            distancesToCheck.forEach( (distance) => {
                let feature = turf.along(lineString, distance, {units: "kilometers"} )
                feature.properties.distanceAlongLine = distance * 1000
                points.push(feature)
            } )     
            return points
        }

        function averageElevation (pointsElevation, basis) { // ex: for a basis of 5, take 5 next altitude points and average them
            var averagedPointsElevation = []
            // Add [basis/2] first points at the start (deal with points which can't be averaged because not enough first points. For them, average will be calculated on available points)
            for (var i = 0; i < Math.ceil(basis / 2); i++) { // i = 1, 2, 3... basis/2
                var firstPoints = []
                for (let j = 0; j <= i; j++) { // i = 0 / j = 0... basis/2
                    firstPoints[j] = pointsElevation[i - j]
                }
                let averagedPoint = Math.abs(Math.floor(calculateAverage(firstPoints)))
                averagedPointsElevation.push(averagedPoint)
            }
            // Add averaged points to averagedPointsElevation array
            for (var i = 0; i < (pointsElevation.length - basis); i++) {
                // Calculate the average of the next [basis] points
                var nextPoints = []
                for (let j = 0; j < basis; j++) { 
                    nextPoints[j] = pointsElevation[i + j]
                }
                let averagedPoint = Math.abs(Math.floor(calculateAverage(nextPoints)))
                averagedPointsElevation.push(averagedPoint)
            }
            // Add [basis/2] last points at the end (deal with points which can't be averaged because not enough next points. For them, average will be calculated on remaining points)
            for (var i = Math.floor(basis / 2); i > 0; i--) { // i = 10, 9, 8, 7... 0
                var lastPoints = []
                for (let j = 0; j < i; j++) { // i = 10 / j = 0, 1, 2, 3... 10
                    lastPoints[j] = pointsElevation[pointsElevation.length - i + j]
                }
                let averagedPoint = Math.abs(Math.floor(calculateAverage(lastPoints)))
                averagedPointsElevation.push(averagedPoint)
            }
            return averagedPointsElevation
        }
    
        function averageElevationFromTips (start, end, index) {
            var section = []
            for (let i = 0; i < index; i++) {
                var point = []
                for (let j = index; j > i; j--) {
                    point.push(start)
                }
                for (let k = 0; k < i; k++) {
                    point.push(end)
                }
                section.push(Math.floor(calculateAverage(point)))
            }
            return section
        }
    }

    async calculateElevation (routeData) {
        var profileData = await this.getProfileData(routeData)
        var elevation = 0
        for (let i = 1; i < profileData.averagedPointsElevation.length - 1; i++) {
            if (profileData.averagedPointsElevation[i] > profileData.averagedPointsElevation[i - 1]) {
                elevation += (profileData.averagedPointsElevation[i] - profileData.averagedPointsElevation[i - 1])
            }
        }
        return elevation
    }

    // Get tunnels info from a mapbox Directions API request result
    getTunnels (directionsRouteData) {
        var tunnels = []
        const section  = directionsRouteData.geometry.coordinates
        for (let l = 0; l < directionsRouteData.legs.length; l++) {   
            const leg = directionsRouteData.legs[l]
            for (let i = 0; i < leg.steps.length; i++) {
                for (let j = 0; j < leg.steps[i].intersections.length; j++) {
                    if (leg.steps[i].intersections[j].classes) {
                        if (leg.steps[i].intersections[j].classes[0] == 'tunnel') {
                            let tunnelStart = leg.steps[i].intersections[j].location
                            if (leg.steps[i].intersections[j + 1]) {
                                var tunnelEnd = leg.steps[i].intersections[j + 1].location
                            } else {
                                var tunnelEnd = leg.steps[i + 1].intersections[0].location
                            }
                            var startClosestSectionCoordinates = CFUtils.closestLocation(tunnelStart, section)
                            var startKey = parseInt(getKeyByValue(section, startClosestSectionCoordinates))
                            var endClosestSectionCoordinates = CFUtils.closestLocation(tunnelEnd, section)
                            var endKey = parseInt(getKeyByValue(section, endClosestSectionCoordinates))
                            var tunnelCoordinates = section.slice(startKey, endKey + 1)
                            tunnels.push(tunnelCoordinates)
                        }
                    }
                }
            }
        }
        return tunnels
    }

    // Paint tunnels on map from an array of coordinate arrays
    paintTunnels (tunnels) {
        this.clearTunnels()
        if (tunnels) {
            tunnels.forEach( (tunnel) => {
                // Prepare layer data
                const tunnelData = {
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
    }

    clearTunnels () {
        // Remove layers
        if (this.map.getLayer('tunnel0')) {
            for (let i = 0; i < this.tunnelNumber; i++) {
                this.map.removeLayer('tunnel' + i)
                this.map.removeSource('tunnel' + i)
            }
        }
        // Reset variable
        this.tunnelNumber = 0
    }

    async flyAlong (routeData) {
        return new Promise ((resolve, reject) => {

            var startFlying = () => {

                var clearAlongRoute = async (routeData) => {
                    return new Promise ( async (resolve, reject) => {
                        // Remove route red gradient property
                        this.map.setPaintProperty('route', 'line-gradient', null)
                        // Clear position marker
                        positionMarker.remove()
                        // Clear start and goal markers
                        this.clearProfileCursor()
                        this.clearTooltip()
                        await this.focus(routeData)
                        if (this.updateMapDataListener) this.updateMapData()
                        if (this.updateMapDataListener) this.hideStartGoalMarkers()
                        if (distanceMarkersOn) this.updateDistanceMarkers()
                        if (this.$map.querySelector('.story-caption')) this.$map.querySelector('.story-caption').remove()
                        document.querySelector('.mapboxgl-ctrl-logo').style.display = 'block'
                        this.$map.classList.remove('map-fullscreen-mode')                        
                        $profile.classList.remove('profile-fullscreen-mode')
                        if ($profilePreviousElementSibling) $profilePreviousElementSibling.after($profile)
                        if (!this.map.style.stylesheet.name.includes('satellite') && this.map.getLayer('satellite')) this.map.setPaintProperty('satellite', 'raster-opacity', [
                            "interpolate",
                            ["exponential", 1.3],
                            ["zoom"],
                            13,
                            0,
                            17,
                            1
                        ] )
                        this.map.resize()
                        end = true
                    } )
                }

                // Clear layers
                if (document.querySelector('#boxShowDistanceMarkers') && document.querySelector('#boxShowDistanceMarkers').checked) {
                    var distanceMarkersOn = true
                    this.hideDistanceMarkers ()
                    console.log(this.map.style.stylesheet.name)
                    if (!this.map.style.stylesheet.name.includes('satellite') && this.map.getLayer('satellite')) { // Hide satellite raster images if style is not a dedicated satellite one for improving smoothness during animation
                        this.map.setPaintProperty('satellite', 'raster-opacity', 0)
                    }
                }
                if (!this.map.getSource('startGoal')) this.displayStartGoalMarkers(routeData)
                // Data setting
                const routeDistance = turf.length(routeData)
                const cameraData = CFUtils.smoothLine(routeData)
                const cameraRouteDistance = turf.length(cameraData)
                const camera = this.map.getFreeCameraOptions()
                if (routeDistance * 1200 < 90000) var animationDuration = routeDistance * 1200
                else var animationDuration = 90000
                let prephase = true
                let prephaseOffset = 0
                let prephaseDistanceOffset
                let start
                let stop
                let end
                // Options
                var cameraOffset = 4 // km
                var cameraAltitude = 800 // m
                // Short and long distance correction settings
                if (routeDistance < 3) {
                    cameraOffset = 0.5
                    cameraAltitude = 400
                    animationDuration = 12000
                } else if (routeDistance > 3 && routeDistance < 10) {
                    cameraOffset = 2
                    cameraAltitude = 600
                    animationDuration = 12000
                } else if (animationDuration === 90000) {
                    cameraOffset = 2
                }
                console.log('cameraOffset : ' + cameraOffset)

                // Go fullscreen
                this.$map.classList.add('map-fullscreen-mode')
                var $profile = document.querySelector('#profileBox')
                $profile.classList.add('profile-fullscreen-mode')
                this.map.resize()
                this.closeControlsOnMobile()
                document.querySelector('.mapboxgl-ctrl-logo').style.display = 'none'
                // If profile is displayed inside a popup, temporary move it outside
                if ($profile.closest('.mapboxgl-popup')) {
                    var $profilePreviousElementSibling = $profile.previousElementSibling
                    document.querySelector('body').appendChild($profile)
                }

                // Build position marker
                const $marker = document.createElement('div')
                $marker.classList = 'fly-along-marker'
                $marker.innerHTML = '<img>'
                var positionMarker = new mapboxgl.Marker($marker)
                positionMarker.setLngLat(routeData.geometry.coordinates[0])
                positionMarker.addTo(this.map)
                // Update marker element with connected user profile picture
                ajaxGetRequest ('/api/map.php' + "?getpropic=" + this.session.id, (src) => {
                    $marker.querySelector('img').style.backgroundImage = 'url(' + src + ')'
                    positionMarker = new mapboxgl.Marker($marker)
                    positionMarker.setLngLat(routeData.geometry.coordinates[0])
                    positionMarker.addTo(this.map)
                } )

                var frame = async (time) => {

                    if (!start) start = time
                    
                    // When stopping, clear layers and put standard route back
                    if (stop) {
                        await clearAlongRoute(routeData)
                        resolve(true)
                    }

                    // Phase (normalized between 0 and 1) determines how far through the animation we are
                    var phase = (time - start) / animationDuration
                    // Case of end of prephase
                    if (prephase && ((cameraRouteDistance * phase) >= cameraOffset)) {
                        prephaseOffset = phase
                        prephaseDistanceOffset = routeDistance * phase
                        start = 0
                        phase = 0
                        prephase = false
                    } // Case of end of animation
                    if (phase > 1) stop = true
                    
                    // Use the phase to get a point that is the appropriate distance along the route
                    // this approach syncs the camera and route positions ensuring they move
                    // at roughly equal rates even if they don't contain the same number of points
                    if (prephase) {
                        var alongCamera    = turf.along(cameraData, 0).geometry.coordinates
                        var awayCamera     = turf.along(cameraData, cameraRouteDistance * phase).geometry.coordinates
                        var alongRoute     = turf.along(routeData, routeDistance * phase).geometry.coordinates
                    } else {
                        var alongCamera    = turf.along(cameraData, cameraRouteDistance * phase).geometry.coordinates
                        var awayCamera     = turf.along(cameraData, (cameraRouteDistance * phase) + prephaseDistanceOffset).geometry.coordinates
                        var alongRoute     = turf.along(routeData, (routeDistance * phase) + prephaseDistanceOffset).geometry.coordinates
                    }

                    // Tell the camera to look at a point along the route
                    camera.lookAtPoint( {
                        lng: awayCamera[0],
                        lat: awayCamera[1]
                    } )
                    
                    // Set the position and altitude of the camera
                    var elevationCorrectorAway = this.map.queryTerrainElevation(awayCamera, {exaggerated: false})
                    var elevationCorrectorAlong = this.map.queryTerrainElevation(alongCamera, {exaggerated: false})
                    if (elevationCorrectorAlong == undefined) elevationCorrectorAlong = elevationCorrectorAway
                    var elevationCorrector = (elevationCorrectorAway + elevationCorrectorAlong) / 2
                    camera.position = mapboxgl.MercatorCoordinate.fromLngLat( 
                        {
                            lng: alongCamera[0],
                            lat: alongCamera[1]
                        },
                        cameraAltitude + elevationCorrector
                    )

                    this.map.setFreeCameraOptions(camera)

                    if (!end) window.requestAnimationFrame(frame)

                    // Color route in red according to process
                    this.map.setPaintProperty('route', 'line-gradient', [
                        'step',
                        ['line-progress'],
                        '#ff5555',
                        phase + prephaseOffset,
                        'blue'
                    ] )

                    // Update position marker
                    positionMarker.setLngLat(CFUtils.replaceOnRoute(alongRoute, routeData))
                    
                    // Draw profile cursor
                    if (!stop) var cursorPosition = this.drawAlongProfileCursor(routeData, alongRoute)
                    
                    // If popup is opened, display tooltip
                    if (document.querySelector('.marker-popup')) {
                        this.clearTooltip()
                        this.drawTooltip(routeData, alongRoute[0], alongRoute[1], cursorPosition, 0, {backgroundColor: 'white'})
                    }

                    /// Set data events
                    // Checkpoints
                    if (this.data) {

                        const currentDistance = routeDistance * (phase + prephaseOffset)
                        const displayStory = (checkpoint) => {
                            // Make sure story is not already displayed
                            if (!this.$map.querySelector('.lightbox-caption') || (this.$map.querySelector('.lightbox-caption') && this.$map.querySelector('.lightbox-caption') && parseInt(this.$map.querySelector('.story-number').innerText) != checkpoint.number)) {
                                var checkpointTime = new Date(checkpoint.datetime)
                                // Remove previous caption element
                                if (this.$map.querySelector('.lightbox-caption')) this.$map.querySelector('.lightbox-caption').remove()
                                // Build caption element
                                var $storyCaption = document.createElement('div')
                                $storyCaption.className = 'lightbox-caption story-caption'
                                var $storyTopLine = document.createElement('div')
                                $storyTopLine.className = 'd-flex gap'
                                var $storyNumber = document.createElement('div')
                                $storyNumber.className = 'lightbox-name story-number'
                                $storyNumber.innerText = checkpoint.number
                                var $storyName = document.createElement('div')
                                $storyName.className = 'lightbox-name'
                                $storyName.innerText = checkpoint.name
                                var $storyData = document.createElement('div')
                                $storyData.className = 'lightbox-location'
                                $storyData.innerText = checkpoint.distance + 'km - ' + checkpointTime.getHours() + 'h' + checkpointTime.getMinutes()
                                var $story = document.createElement('div')
                                $story.className = 'lightbox-story'
                                $story.innerText = checkpoint.story
                                $storyTopLine.appendChild($storyNumber)
                                $storyTopLine.appendChild($storyName)
                                $storyCaption.appendChild($storyTopLine)
                                $storyCaption.appendChild($storyData)
                                $storyCaption.appendChild($story)
                                // Append it to map element
                                this.$map.appendChild($storyCaption)
                            }
                        }

                        // Checkpoints
                        if (this.data.checkpoints) {
                            this.data.checkpoints.forEach( (checkpoint) => {
                                // If marker has entered displayRange and alongRoute length is not too far from checkpoint distance, display story
                                if (currentDistance > checkpoint.distance) displayStory(checkpoint)
                            } )
                        }
                        // Photos
                        if (this.data.photos) {
                            const displayRange = 1.5 * animationDuration / routeDistance / 1000 // km - Maximum distance current point and photo can be separated on the line
                            this.data.photos.forEach( (photo) => {
                                if (Math.abs(photo.distance - currentDistance) < displayRange && !photo.marker.getElement().classList.contains('half-grown')) {
                                    this.data.photos.forEach(otherPhoto => otherPhoto.marker.getElement().classList.remove('half-grown')) // Ensure that two close photos will not display at the same time
                                    photo.marker.getElement().classList.add('half-grown')
                                } else if (Math.abs(photo.distance - currentDistance) > displayRange  && photo.marker.getElement().classList.contains('half-grown')) {
                                    photo.marker.getElement().classList.remove('half-grown')
                                }
                            } )
                        }
                    }
                }

                // Stop the animation on mouse down
                this.map.once('mousedown', () => {
                    stop = true
                    clearAlongRoute(routeData)
                    resolve(true)
                })

                window.requestAnimationFrame(frame)
            }

            // Start flying along when all map tiles are fully loaded
            if (!this.map.areTilesLoaded()) this.map.once('idle', startFlying)
            else startFlying()

        } )
    }

    clearProfileCursor () {
        const profileElement = document.getElementById('elevationProfile')
        const ctx            = profileElement.getContext('2d')
        ctx.strokeStyle = 'white'
        ctx.lineWidth = 10
        ctx.beginPath()
        ctx.moveTo(0, 1)
        ctx.lineTo(profileElement.offsetWidth, 1)
        ctx.stroke()
    }

    // Draw a horizontal line reflecting current position along the route
    drawAlongProfileCursor (routeData, alongRoute) {
        const offsetLeft = 50
        const offsetRight = 20
        const profileElement = document.getElementById('elevationProfile')
        const ctx            = profileElement.getContext('2d')
        var currentSectionDistance = turf.length(turf.lineSlice(routeData.geometry.coordinates[0], alongRoute, routeData))
        var routeDistance    = turf.length(routeData)
        var coursePosition   = currentSectionDistance / routeDistance
        var cursorPosition   = (coursePosition * (profileElement.offsetWidth - offsetLeft - offsetRight))
        ctx.strokeStyle = '#ff5555'
        ctx.lineWidth   = 3 
        ctx.beginPath()
        ctx.moveTo(offsetLeft, 3)
        ctx.lineTo(offsetLeft + cursorPosition, 3)
        ctx.stroke()
        return cursorPosition
    }

    // Set map bounds according to current route
    async focus (routeData) {
        return new Promise ((resolve, reject) => {
            if (routeData) var routeBounds = CFUtils.defineRouteBounds(routeData.geometry.coordinates)
            else if (!routeData && this.map.getSource('startPoint')) var routeBounds = CFUtils.defineRouteBounds([this.map.getSource('startPoint')._data.features[0].geometry.coordinates])
            this.map.fitBounds(routeBounds)
            this.map.once('idle', () => {
                resolve(true)
            } )
        } )
    }

    // Set another map style without interfering with current route
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
        } )
    }

    // Return currently displayed route related layers
    saveRouteStyle () {
        // Route
        if (this.map.getSource('route')) var route = this.map.getSource('route')._data
        if (this.map.getLayer('route-cap')) var routeCap = true
        // Startpoint
        if (this.map.getSource('startPoint')) var startPoint = this.map.getSource('startPoint')._data
        // Endpoint
        if (this.map.getSource('endPoint')) var endPoint = this.map.getSource('endPoint')._data
        // Waypoints
        var wayPoints = []
        let s = 2
        while (this.map.getSource('wayPoint' + s)) {
            wayPoints.push(this.map.getSource('wayPoint' + s)._data)
            s++
        }

        // Return it inside an object
        return {
            route: route,
            routeCap: routeCap,
            startPoint: startPoint,
            endPoint: endPoint,
            wayPoints: wayPoints
        }
    }


    // Load and display previously saved route related layers
    async loadRouteStyle (routeStyle) {
        if (routeStyle.route) {
            this.map.addSource('route', {
                type: 'geojson',
                lineMetrics: true,
                data: routeStyle.route
            } )
            if (routeStyle.routeCap) {
                this.map.addLayer( {
                    id: 'route-cap',
                    type: 'line',
                    source: 'route',
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': this.routeCapColor,
                        'line-width': this.routeWidth / 2,
                        'line-opacity': 1,
                        'line-gap-width': 3,
                    }
                } )
            }
            this.map.addLayer( {
                id: 'route',
                type: 'line',
                source: 'route',
                layout: {
                    'line-join': 'round',
                    'line-cap': 'round'
                },
                paint: {
                    'line-color': this.routeColor,
                    'line-width': this.routeWidth,
                    'line-opacity': 1,
                }
            } )
            this.paintTunnels(this.map.getSource('route')._data.properties.tunnels)
        }
        if (routeStyle.startPoint) {
            this.start = routeStyle.startPoint.features[0].geometry.coordinates
            this.map.addSource('startPoint', {
                type: 'geojson',
                data: routeStyle.startPoint
            } )
            this.map.addLayer( {
                id: 'startPoint',
                type: 'circle',
                source: 'startPoint',
                paint: {
                    'circle-radius': 8,
                    'circle-color': '#afffaa',
                    'circle-stroke-color': this.routeColor,
                    'circle-stroke-width': 2
                }
            } )
        }
        if (routeStyle.endPoint) {
            this.map.addSource('endPoint', {
                type: 'geojson',
                data: routeStyle.endPoint
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
        if (routeStyle.wayPoints) {
            this.waypointNumber = 1
            for (let i = 0; i < routeStyle.wayPoints.length; i++) {
                this.waypointNumber = this.prepareNextWaypoint(this.waypointNumber)
                this.map.addSource('wayPoint' + (i + 2), {
                    type: 'geojson',
                    data: routeStyle.wayPoints[i]
                } )
                this.map.addLayer( {
                    id: 'wayPoint' + (i + 2),
                    type: 'circle',
                    source: 'wayPoint' + (i + 2),
                    paint: {
                        'circle-radius': 4,
                        'circle-color': 'white',
                        'circle-stroke-color': this.routeColor,
                        'circle-stroke-width': 1
                    }
                } )
            }
        }
    }

    // Remove current route
    clearRoute () {
        if (this.map.getSource('startPoint')) {
            this.map.removeLayer('startPoint')
            this.map.removeSource('startPoint')
        }
        if (this.map.getSource('endPoint')) {
            this.map.removeLayer('endPoint')
            this.map.removeSource('endPoint')
        }
        if (this.map.getSource('startGoal')) {
            this.map.removeLayer('startGoal')
            this.map.removeSource('startGoal')
        }
        for (let i = 2; i <= this.waypointNumber; i++) {
            this.map.removeLayer('wayPoint' + i)
            this.map.removeSource('wayPoint' + i)
        }
        if (this.map.getSource('route')) {
            this.map.removeLayer('route')
            if (this.map.getLayer('route-cap')) this.map.removeLayer('route-cap')
            this.map.removeSource('route')
            if (document.querySelector('#buttonSave')) document.querySelector('#buttonSave').setAttribute('disabled', 'disabled')
        }
        if (this.map.getLayer('profilePoint')) {
            this.map.removeLayer('profilePoint')
            this.map.removeSource('profilePoint')
        }
        this.clearTunnels()
        this.waypointNumber = 0
        if (document.querySelector('#buttonClear')) document.querySelector('#buttonClear').setAttribute('disabled', 'disabled')
        this.start = null
        this.hideDistanceMarkers()
    }

    // Get geolocation of a LngLat point
    async getCourseGeolocation (lngLat) {
        return new Promise ((resolve, reject) => {
            if (lngLat.lng) var lng = lngLat.lng
            else var lng = lngLat[0]
            if (lngLat.lat) var lat = lngLat.lat
            else var lat = lngLat[1]
            ajaxGetRequestForLocation ('https://api.mapbox.com/search/v1/reverse/' + lng + ',' + lat + '?language=ja&access_token=' + this.apiKey, callback)
            
            function ajaxGetRequestForLocation (url, requestCallback) {
                var xhr = getHttpRequest()
                xhr.onreadystatechange = function () {
                    if (xhr.readyState === 4) {
                        // If an error has occured during the request
                        if (xhr.status != 200) {
                            console.log('An error has occured during the request.')
                        } // If the request have been performed successfully
                        else {
                            var response = JSON.parse(xhr.responseText)
                        
                            // Treat response
                            requestCallback(response)
                        }
                    }
                }
                // Send request through POST method
                xhr.open('GET', url, true)
                xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')
                xhr.send()
            }

            function callback (response) {
                console.log('MAPBOX GEOCODING API USE +1')
                var geolocation = CFUtils.reverseGeocoding(response)
                resolve (geolocation)
            }
        } )
    }

    setCheckpointNumber (checkpoints, current) {
        // Get current distance from start
        const routeDistance = turf.length(this.map.getSource('route')._data)
        let point = turf.point([current.lng, current.lat])
        let subline = turf.lineSlice(turf.point(this.map.getSource('route')._data.geometry.coordinates[0]), point, this.map.getSource('route')._data)
        const currentDistance = turf.length(subline)
        // If situated at the very beginning of the route, return 0 (as start)
        if (currentDistance == 0) return 0
        // If situated at the very end of the route, return last checkpoint number (as goal)
        else if (Math.round(currentDistance * 10) / 10 == Math.round(routeDistance * 10) / 10) return checkpoints.length - 1
        // Else, return current position on the route among other checkpoints
        else {
            var number
            checkpoints.forEach( (checkpoint) => {
                if (checkpoint.distance < currentDistance) {
                    number = checkpoint.number + 1
                }
            } )
            return number
        }
    }

    // Sort markers array by distance from start of lineString
    sortMarkers (lineString, markers) {
        markers.forEach( (marker) => {
            var lngLat = marker._lngLat
            let point = turf.point([lngLat.lng, lngLat.lat])
            let subline = turf.lineSlice(turf.point(lineString.geometry.coordinates[0]), point, lineString)
            marker.distance = turf.length(subline)
        } )
        // Sort markers
        markers.sort( (a, b) => {
            return a.distance - b.distance
        } )
        return markers
    }

    updateMarkers (options = {exceptSF: true}) {
        this.data.checkpoints.forEach( (checkpoint) => {
            if (checkpoint.marker) {
                var markerElement = checkpoint.marker.getElement()
                // If except SF option is on, don't update start nor finish marker
                if (options.exceptSF) {
                    if (!(markerElement.classList.contains('checkpoint-marker-start') || markerElement.classList.contains('checkpoint-marker-goal'))) {
                        markerElement.innerHTML = checkpoint.number
                    }
                // Else, don't update start marker
                } else {
                    if (!(markerElement.classList.contains('checkpoint-marker-start'))) { 
                        markerElement.innerHTML = checkpoint.number
                        markerElement.classList.remove('checkpoint-marker-goal')
                    }
                }
            }
        } )
    }

    sortCheckpoints (lineString) {
        var checkpoints = this.data.checkpoints
        if (checkpoints) {
            // Get each point distance
            checkpoints.forEach( (checkpoint) => {
                if (!checkpoint.distance) {
                    if ((Math.round(checkpoint.lngLat.lng * 1000) / 1000 != Math.round(lineString.geometry.coordinates[0][0] * 1000) / 1000) && (Math.round(checkpoint.lngLat.lat * 1000) / 1000 != Math.round(lineString.geometry.coordinates[0][1] * 1000) / 1000)) {
                        let point = turf.point([checkpoint.lngLat.lng, checkpoint.lngLat.lat])
                        let subline = turf.lineSlice(turf.point(lineString.geometry.coordinates[0]), point, lineString)
                        checkpoint.distance = turf.length(subline)
                    } else checkpoint.distance = 0 // If checkpoint has the same coordinates (at a 0.001 precision) as linestring first waypoint, then automatically set distance to 0
                }
            } )
            // Sort checkpoints
            checkpoints.sort( (a, b) => {
                return a.distance - b.distance
            } )
            // Update element except for start and goal 
            var i = 0
            checkpoints.forEach( (checkpoint) => {
                checkpoint.number = i
                if (checkpoint.form) checkpoint.form.id = 'checkpointForm' + i
                i++
            } )
        }
        // Update data
        this.data.checkpoints = checkpoints
    }

    setGrabber () {

        var grabber = document.querySelector('.grabber')
        let y = 0 // Current height of the mouse
        let h = 0 // Height of the map element
        
        const mouseDownHandler = (e) => {
            // Get positions
            y = e.clientY
            const styles = window.getComputedStyle(this.$map.parentElement)
            h = parseInt(styles.height, 10)
            // Attach listeners to the document
            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
        }

        const mouseMoveHandler = (e) => {
            const dy = e.clientY - y // How far the mouse has been moved
            // Only resize element height if between 200 and window inner height
            if (h + dy > 200 && e.clientY < (window.innerHeight - parseInt(grabber.offsetHeight))) this.$map.parentElement.style.height = `${h + dy}px`
        }
        
        const mouseUpHandler = () => {
            // Remove the handlers of `mousemove` and `mouseup`
            document.removeEventListener('mousemove', mouseMoveHandler)
            document.removeEventListener('mouseup', mouseUpHandler)
            // Resize map and focus if map instance argument has been provided
            this.map.resize()
            this.focus(this.data.routeData)
        }

        grabber.addEventListener('mousedown', mouseDownHandler)
    }

    inViewedMkpointsList (mkpoint) {
        var activity_id = false
        this.clearedMkpoints.forEach( (clearedMkpoint) => {
            if (clearedMkpoint.id == mkpoint.id) activity_id = clearedMkpoint.activity_id
        } )
        return activity_id
    }
}


/*
// Correct left offset depending on whether closest container will be counted or not
if (this.type = "route") var pointX = e.x + document.querySelector('#profileBox').offsetLeft
else var pointX = e.x - document.querySelector('#profileBox').offsetLeft
*/