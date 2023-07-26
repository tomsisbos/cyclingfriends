///import MapboxGeocoder from '/node_modules/@mapbox/mapbox-gl-geocoder/dist/mapbox-gl-geocoder.min.js'
import CFUtils from "/class/utils/CFUtils.js"
import CFSession from "/class/utils/CFSession.js"
import Profile from "/class/Profile.js"
import Model from "/class/Model.js"
import SceneryPopup from "/class/maps/scenery/SceneryPopup.js"
import ActivityPhotoPopup from "/class/maps/activity/ActivityPhotoPopup.js"

// Global class initialization
export default class Map extends Model {

    constructor () {
        super()
        this.setSeason()
        this.centerOnUserLocation()
    }

    apiUrl = '/api/map.php'
    map
    $map
    mapdata = {}
    profile
    sceneriesMarkerCollection = []
    sceneriesZoomRoof = 7 // Scenery display minimum zoom level
    sceneriesMinNumber = 20 // Number of sceneries displayed to try to reach at minimum
    sceneriesMaxNumber = 40 // Maximum number of sceneries displayed at the same time
    activityPhotosMarkerCollection = []
    activityPhotosZoomRoof = 12 // Activity photos display minimum zoom level
    selectStyle
    dislayKonbinisBox
    displayAmenitiesBox
    displaySceneriesBox
    displayActivityPhotosBox
    loaded = false
    defaultCenter = [139.7673068, 35.6809591]
    defaultZoom = 10
    defaultPitch = 0
    sceneries
    tunnelNumber = 0
    month = new Date().getMonth() + 1
    season
    routeColor = '#0000ff'
    routeCapColor = '#fff'
    routeWidth = 5
    segmentLocalColor = '#8bffff'
    segmentRegionalColor = '#2bffff'
    segmentNationalColor = '#2bc8ff'
    segmentCapColor = '#fff'
    segmentSeasonColor = '#ff5555'
    
    async centerOnUserLocation () {
        this.map.setCenter(await this.getUserLocation())
    }

    async getUserLocation () {
        return new Promise((resolve, reject) => {
            // On first load, query user location data and store it in the browser
            if (!localStorage.getItem('userLocationLng') || !localStorage.getItem('userLocationLat')) {
                CFSession.get('lngLat').then(response => {
                    if (response) var userLocation = [response.lng, response.lat]
                    else var userLocation = this.defaultCenter
                    localStorage.setItem('userLocationLng', userLocation[0])
                    localStorage.setItem('userLocationLat', userLocation[1])
                    resolve(userLocation)
                } )
            // If entry already exists in local storage, use it
            } else resolve([parseFloat(localStorage.getItem('userLocationLng')), parseFloat(localStorage.getItem('userLocationLat'))])
        })
    }

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

    clearMapData () {
        return true
    }

    addController () {
        var controller = document.createElement('div')
        controller.className = 'map-controller map-controller-left'
        this.$map.querySelector('.mapboxgl-ctrl-top-left').appendChild(controller)
        return controller
    }

    addStyleControl () {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
        else var controller = this.addController()
        // Add style control
        var selectStyleContainer = document.createElement('div')
        selectStyleContainer.className = 'map-controller-block bold'
        controller.appendChild(selectStyleContainer)
        var selectStyleLabel = document.createElement('div')
        selectStyleLabel.innerText = '地図 : '
        selectStyleContainer.appendChild(selectStyleLabel)
        this.selectStyle = document.createElement('select')
        var seasons = document.createElement("option")
        var satellite = document.createElement("option")
        seasons.value = 'seasons'
        seasons.text = '季節'
        seasons.setAttribute('selected', 'selected')
        seasons.id = 'cl07xga7c002616qcbxymnn5z'
        satellite.id = 'cl0chu1or003a15nocgiodiir'
        satellite.value = 'satellite'
        satellite.text = '航空写真'
        this.selectStyle.add(seasons)
        this.selectStyle.add(satellite)
        this.selectStyle.className = 'js-map-styles'
        selectStyleContainer.appendChild(this.selectStyle)
        this.selectStyle.onchange = (e) => {
            var index = e.target.selectedIndex
            var layerId = e.target.options[index].id
            if (layerId === 'seasons') layerId = this.season
            this.setMapStyle(layerId)
        }
    }

    addOptionsControl () {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
        else var controller = this.addController()
        // Add style control
        // Map options container
        var optionsContainer = document.createElement('div')
        optionsContainer.className = 'map-controller-block flex-column'
        controller.appendChild(optionsContainer)
        // Label
        var mapOptionsLabel = document.createElement('div')
        mapOptionsLabel.innerText = '地図設定'
        mapOptionsLabel.className = 'map-controller-label'
        optionsContainer.appendChild(mapOptionsLabel)
        // Line 1
        let line1 = document.createElement('div')
        line1.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line1)
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
        dislayKonbinisBoxLabel.innerText = 'コンビニを表示'
        line1.appendChild(dislayKonbinisBoxLabel)
        // Line 2
        let line2 = document.createElement('div')
        line2.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line2)
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
        displayAmenitiesBoxLabel.innerText = 'アメニティを表示'
        line2.appendChild(displayAmenitiesBoxLabel)
        // Line 3
        let line3 = document.createElement('div')
        line3.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line3)
        this.displayActivityPhotosBox = document.createElement('input')
        this.displayActivityPhotosBox.id = 'displayActivityPhotosBox'
        this.displayActivityPhotosBox.setAttribute('type', 'checkbox')
        this.displayActivityPhotosBox.setAttribute('checked', 'true')
        line3.appendChild(this.displayActivityPhotosBox)
        this.displayActivityPhotosBox.addEventListener('click', () => {
            if (this.displayActivityPhotosBox.checked) this.updateActivityPhotos()
            else this.activityPhotosMarkerCollection.forEach( (marker) => marker.remove())
            this.activityPhotosMarkerCollection = []
        } )
        var displayActivityPhotosBoxLabel = document.createElement('label')
        displayActivityPhotosBoxLabel.setAttribute('for', 'displayActivityPhotosBox')
        displayActivityPhotosBoxLabel.innerText = 'アクティビティ写真を表示'
        line3.appendChild(displayActivityPhotosBoxLabel)
        
        // Hide and open on click on mobile display
        mapOptionsLabel.addEventListener('click', () => {
            optionsContainer.querySelectorAll('.map-controller-line').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    mapOptionsLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )
    }

    addRouteControl (options = {flyAlong: true, displaySceneries: true}) {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
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
        boxSet3DLabel.setAttribute('for', 'boxSet3D')
        line4.appendChild(boxSet3DLabel)
        boxSet3D.addEventListener('change', () => {
            if (boxSet3D.checked) {
                this.map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 1})
                this.map.setPitch(this.defaultPitch)
            } else {
                this.map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 0})
                this.map.setPitch()
            }
        } )
        // Line 5
        if (options.displaySceneries) {
            let line5 = document.createElement('div')
            line5.className = 'map-controller-line hide-on-mobiles'
            routeContainer.appendChild(line5)
            this.displaySceneriesBox = document.createElement('input')
            this.displaySceneriesBox.id = 'displaySceneriesBox'
            this.displaySceneriesBox.setAttribute('type', 'checkbox')
            this.displaySceneriesBox.setAttribute('checked', 'checked')
            line5.appendChild(this.displaySceneriesBox)
            var displaySceneriesBoxLabel = document.createElement('label')
            displaySceneriesBoxLabel.innerText = '絶景スポットを表示'
            displaySceneriesBoxLabel.setAttribute('for', 'displaySceneriesBox')
            line5.appendChild(displaySceneriesBoxLabel)
            this.displaySceneriesBox.addEventListener('change', () => {
                if (this.displaySceneriesBox.checked) {
                    this.addSceneries(this.mapdata.sceneries)
                    if (document.querySelector('.rt-slider')) document.querySelector('.rt-slider').style.display = 'flex'
                    this.profile.generate({
                        poiData: {
                            sceneries: this.mapdata.sceneries
                        }
                    })
                } else {
                    this.hideSceneries()
                    if (document.querySelector('.rt-slider')) document.querySelector('.rt-slider').style.display = 'none'
                    this.profile.generate()
                }
            } )
        }
        // Camera buttons
        let line6 = document.createElement('div')
        line6.className = 'map-controller-line hide-on-mobiles'
        routeContainer.appendChild(line6)
        // Focus button
        var buttonFocus = document.createElement('button')
        buttonFocus.className = 'map-controller-block mp-button mp-button-small'
        buttonFocus.id = 'buttonFocus'
        buttonFocus.innerText = '全体表示'
        line6.appendChild(buttonFocus)
        buttonFocus.addEventListener('click', () => {
            this.focus(this.map.getSource('route')._data)
        } )
        // Fly button
        if (options.flyAlong) {
            var buttonFly = document.createElement('button')
            buttonFly.className = 'map-controller-block mp-button mp-button-small'
            buttonFly.id = 'buttonFly'
            buttonFly.innerText = '走行再現'
            line6.appendChild(buttonFly)
            buttonFly.addEventListener('click', async () => {
                if (this.map.getSource('route')) this.flyAlong(await this.getRouteData())
            } )
        }
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

    /**
     * Add a search control to the map
     */
    addSearchControl () {
        require(["/node_modules/@mapbox/mapbox-gl-geocoder/dist/mapbox-gl-geocoder.min.js"], (MapboxGeocoder) => {
            const searchBar = new MapboxGeocoder({
                accessToken: this.apiKey,
                marker: false
            })
            searchBar.addTo('.map-controller-left')
        })
    }
    
    addFullscreenControl () {
        this.map.addControl(
            new mapboxgl.FullscreenControl( {
                container: this.$map
            } )
        )
    }

    async load (element, style, center = null) {
        return new Promise(async (resolve, reject) => {
            if (center == null) center = await this.getUserLocation()
            this.$map = element
            this.map = new mapboxgl.Map ( {
                container: element,
                center,
                zoom: this.defaultZoom,
                style: style,
                preserveDrawingBuffer: true,
                accessToken: this.apiKey,
                attributionControl: false
            } )
            this.map.setPitch(this.defaultPitch)

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
                resolve(this.map)
                this.styleSeason()
                this.loadTerrain()
                this.loadImages()
            } )

            // Instanciate profile
            this.profile = new Profile(this.map)
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
        
        var getKonbiniExpressionArray = (keyword, name) => {
            var array = this.konbiniSearchNames[name]
            return [keyword, ...array]
        }
        
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
                getKonbiniExpressionArray('any', 'seven-eleven'),
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
                        getKonbiniExpressionArray('any', 'family-mart'),
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
                        getKonbiniExpressionArray('any', 'family-mart'),
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
                getKonbiniExpressionArray('any', 'lawson'),
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
                getKonbiniExpressionArray('any', 'mini-stop'),
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
                getKonbiniExpressionArray('any', 'daily-yamazaki'),
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

    getSeasonalColors (season) {
        // Define seasonnal colors
        if (season == 'winter') {
            return [
                "match",
                ["get", "class"],
                "snow",
                "#fff",
                ["wood"],
                "#c8d28e",
                ["grass"],
                "#efe6d2",
                ["scrub"],
                "#bdd08a",
                "#d6cac2"
            ]
        } else if (season == 'spring') {
            return [
                "match",
                ["get", "class"],
                "snow",
                "#fff",
                ["wood"],
                "#33ff05",
                ["grass"],
                "#a1fe7c",
                ["scrub"],
                "#ffb8b8",
                "#fff"
            ]
        } else if (season == 'summer') {
            return [
                "match",
                ["get", "class"],
                "snow",
                "#fff",
                ["wood"],
                "#2e8500",
                ["grass"],
                "#baff6b",
                ["scrub"],
                "#57ff24",
                "#d6cac2"
            ]
        } else if (season == 'fall') {
            return [
                "match",
                ["get", "class"],
                "snow",
                "#fff",
                ["wood"],
                "#f0a884",
                ["grass"],
                "#b3e580",
                ["scrub"],
                "#90c752",
                "#d6cac2"
            ]
        }
    }

    styleSeason () {

        var colors = this.getSeasonalColors(this.season)

        if (this.map.getLayer('landcover-season')) this.map.setPaintProperty('landcover-season', 'fill-color', colors)
        else {
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
            }, 'landuse')
        }

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
                if (zoomLevel < 6) return 100
                if (distance <= 10) {
                    if (zoomLevel > 11) return 1
                    else if (zoomLevel > 6) return 2
                    else return 5
                } else if (distance <= 50 && distance > 10) {
                    if (zoomLevel > 14) return 2
                    else if (zoomLevel > 11) return 5
                    else return 10
                } else if (distance > 50) {
                    if (zoomLevel > 15) return 2
                    else if (zoomLevel > 13) return 5
                    else if (zoomLevel > 11) return 10
                    else return 20
                }
            }
        }
    }

    scaleMarkerAccordingToZoom (element) {
        var zoom = this.map.getZoom()
        var size = zoom * 3 - 15
        if (size < 15) size = 15
        element.style.height = size + 'px'
        element.style.width = size + 'px'
        element.style.border = size/15 + 'px solid white'
    }

    /**
     * Scale (style) marker element according to zoom
     * @param {HTMLElement} element 
     * @param {Object} options
     * @param {Boolean} options.img Whether icon is an image or not
     */
    scaleActivityPhotoMarkerAccordingToZoom (element, options = {img: true}) {
        var zoom = this.map.getZoom()
        if (options.img) var size = zoom * 3 - 15
        else var size = (zoom * 7 - 35) / 5
        if (size < 5) size = 5
        element.style.height = size + 'px'
        element.style.width = size + 'px'
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

    async loadCloseSceneries (range, options = {displayOnMap: true, generateProfile: true}) {
        return new Promise ( async (resolve, reject) => {

            // Display close sceneries inside the map
            ajaxGetRequest ('/api/map.php' + "?display-sceneries=" + this.routeId + '&details=true', async (response) => {

                var sceneries = await this.getClosestSceneries(response, range)

                // Display on map
                if (options.displayOnMap) this.addSceneries(sceneries)

                // Update sceneries cursors on profile
                if (options.generateProfile) this.profile.generate()
                
                // Display thumbnails
                // Get sceneries on route number
                sceneries.forEach( (scenery) => {
                    if (scenery.on_route) this.sceneriesOnRouteNumber++
                } )

                // Get most relevant image url for each scenery and add it to map instance data
                var closestPhotos = await this.getClosestPhotos(sceneries)
                closestPhotos.forEach(photo => {
                    sceneries.forEach(scenery => {
                        if (scenery.id == photo.id) scenery.file_url = photo.data.url
                    } )
                } )
                resolve(sceneries)
            } )
        } )
    }

    async getClosestPhotos (sceneries) {
        return new Promise ( async (resolve, reject) => {
            var ids = sceneries.map(scenery => scenery.id);
            var ids_list = ids.join(',')
            ajaxGetRequest ('/api/map.php' + "?sceneries-closest-photo=" + ids_list, async (response) => {
                resolve(response)
            } )
        } )
    }
    
    async getClosestSceneries (sceneries, range) {
        return new Promise ( async (resolve, reject) => {

            const remotenessTolerance = 0.1
            var sceneriesInRange = []
            var closeSceneries = []

            // Get route
            const routeData = await this.getRouteData()

            // Build a simplified line for rough filtering
            var coreLine = turf.simplify(routeData, {tolerance: 0.02, highQuality: false, mutate: false})
            sceneries.forEach( (scenery) => {
                var point = turf.point([scenery.lng, scenery.lat])
                var nearestLinePoint = turf.nearestPointOnLine(coreLine, point)
                var roughRemoteness = nearestLinePoint.properties.dist
                if (range < 3) var roughRange = range * 8 // Define range from the coreline where to keep sceneries according range value to prevent too small range
                else var roughRange = range * 3
                if (roughRemoteness < roughRange) {
                    sceneriesInRange.push(scenery)
                }
            } )

            // Get route remoteness
            sceneriesInRange.forEach( (scenery) => {
                var point = turf.point([scenery.lng, scenery.lat])
                var nearestLinePoint = turf.nearestPointOnLine(routeData, point)
                scenery.remoteness = nearestLinePoint.properties.dist
                scenery.distance = nearestLinePoint.properties.location
                if (scenery.remoteness < range) {
                    if (scenery.remoteness < remotenessTolerance) scenery.on_route = true
                    else scenery.on_route = false
                    closeSceneries.push(scenery)
                }
            } )
            // Sort sceneries by distance
            closeSceneries.sort( (sceneryA, sceneryB) => sceneryA.distance - sceneryB.distance)

            resolve(closeSceneries)
        } )
    }

    displaySegment (segment) {

        // Build geojson
        var geojson = {
            type: 'Feature',
            properties: {
                rank: segment.rank,
                name: segment.name,
                tags: [],
                tunnels: segment.tunnels
            },
            geometry: {
                type: 'LineString',
                coordinates: segment.coordinates
            }
        }
        segment.tags.forEach(tag => geojson.properties.tags.push(tag))

        if (!this.map.getSource('segment' + segment.id) && !this.map.getLayer('segment' + segment.id)) {

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
    }

    getFittingSegments () {

        return new Promise ( async (resolve, reject) => {

            // Get segments fitting the route
            ajaxGetRequest ('/api/map.php' + "?display-segments=" + this.routeId, async (segments) => {

                const remotenessTolerance = 0.1
                const range = 2
                var segmentsInRange = []
                var fittingSegments = []

                // Get route
                const routeData = await this.getRouteData()

                // Build a simplified line for rough filtering
                var coreLine = turf.simplify(routeData, {tolerance: 0.02, highQuality: false, mutate: false})
                segments.forEach( (segment) => {
                    var point = turf.point([segment.coordinates[0][0], segment.coordinates[0][1]])
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
                    var point = turf.point([segment.coordinates[0][0], segment.coordinates[0][1]])
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

    addSceneries (sceneries) {
        sceneries.forEach( async (scenery) => {

            // Build element
            let element = document.createElement('div')
            let icon = document.createElement('img')
            icon.src = 'data:image/jpeg;base64,' + scenery.thumbnail
            icon.classList.add('scenery-icon')
            if (scenery.on_route === true) icon.style.boxShadow = '0 0 1px 3px ' + this.routeColor
            if (scenery.isCleared) element.classList.add('visited-marker') // Highlight if visited
            if (scenery.isFavorite) element.classList.add('favoured-marker') // Highlight if favoured
            element.appendChild(icon)
            this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
            var marker = new mapboxgl.Marker ( {
                anchor: 'center',
                color: '#5e203c',
                draggable: false,
                element: element
            } )
            marker.popularity = scenery.popularity // Append popularity data to the marker allowing popularity zoom filtering
            marker.isFavorite = scenery.isFavorite // Append favorites list data
            marker.setLngLat([scenery.lng, scenery.lat])
            marker.addTo(this.map)
            marker.getElement().id = 'scenery' + scenery.id
            marker.getElement().classList.add('scenery-marker')
            marker.getElement().dataset.id = scenery.id
            marker.getElement().dataset.user_id = scenery.user_id
            
            // Build and attach popup
            var popupOptions = {
                maxWidth: '250px'
            }
            var instanceOptions = {}
            var instanceData = {
                mapInstance: this,
                scenery
            }
            let sceneryPopup = new SceneryPopup(popupOptions, instanceData, instanceOptions)
            marker.setPopup(sceneryPopup.popup)
        } )
    }

    hideSceneries () {
        let i = 0
        while (i < this.map._markers.length) {
            if (this.map._markers[i]._element.classList.contains('scenery-marker')) this.map._markers[i].remove()
            else i++
        }
        if (this.sceneriesMarkerCollection) this.sceneriesMarkerCollection = []
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

    async flyAlong (routeData, options = {layerId: 'route'}) {
        return new Promise ((resolve, reject) => {

            var startFlying = () => {

                var clearAlongRoute = async (routeData) => {
                    return new Promise ( async (resolve, reject) => {
                        // Remove route red gradient property
                        if (this.map.getLayer(options.layerId)) this.map.setPaintProperty(options.layerId, 'line-gradient', null)
                        // Clear position marker
                        positionMarker.remove()
                        // Clear start and goal markers
                        this.clearProfileCursor()
                        this.clearTooltip()
                        await this.focus(routeData)
                        if (this.updateMapData) this.updateMapData()
                        this.hideStartGoalMarkers()
                        if (distanceMarkersOn) this.updateDistanceMarkers()
                        if (this.$map.querySelector('.story-caption')) this.$map.querySelector('.story-caption').remove()
                        document.querySelectorAll('.half-grown').forEach(grownPhoto => grownPhoto.classList.remove('half-grown'))
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
                // If user is connected, update marker element with connected user profile picture
                CFSession.getPropic().then(src => {
                    if (src) {
                        $marker.querySelector('img').style.backgroundImage = 'url(' + src + ')'
                        positionMarker = new mapboxgl.Marker($marker)
                        positionMarker.setLngLat(routeData.geometry.coordinates[0])
                        positionMarker.addTo(this.map)
                    // Else, simply build marker element
                    } else {
                        positionMarker = new mapboxgl.Marker($marker)
                        positionMarker.setLngLat(routeData.geometry.coordinates[0])
                        positionMarker.addTo(this.map)
                    }
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
                    if (this.map.getLayer(options.layerId)) this.map.setPaintProperty(options.layerId, 'line-gradient', [
                        'step',
                        ['line-progress'],
                        '#ff5555',
                        phase + prephaseOffset,
                        '#0000FF'
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
        ctx.strokeStyle = '#fff'
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
            this.map.once('idle', () => resolve(true))
        } )
    }

    // Set another map style without interfering with current route
    setMapStyle (layerId) {

        // Save layers
        var mapStyle = this.saveMapStyle()
        var routeStyle = this.saveRouteStyle()

        // Clear route
        this.clearRoute()

        // Change map style
        this.map.setStyle('mapbox://styles/sisbos/' + layerId).once('idle', async () => {
            this.loadImages()
            this.addSources()
            this.addLayers()
            this.loadMapStyle(mapStyle)
            this.loadRouteStyle(routeStyle)
        } )
    }

    /**
     * Return currently displayed style specific layers
     * @returns {Object} layer properties
     */
    saveMapStyle () {
        if (this.map.getLayer('landcover-season')) var seasons = true

        // Return it inside an object
        return {seasons}
    }

    /**
     * Return currently displayed route related layers
     * @returns {Object} layer properties
     */
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
        return {route, routeCap, startPoint, endPoint, wayPoints}
    }

    /** 
     * Load and display previously saved route related layers
     * @param {Object} routeStyle Object containing layers
     */
    loadMapStyle (routeStyle) {
        if (routeStyle.seasons) this.styleSeason()
    }

    /** 
     * Load and display previously saved route related layers
     * @param {Object} routeStyle Object containing layers
     */
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
                        'circle-color': '#fff',
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
        if (this.profile) this.profile.clearData()
        if (document.querySelector('#profileBox')) this.hideProfile()
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
        // If first setting of goal, return 1
        else if (Math.round(currentDistance * 10) / 10 == Math.round(routeDistance * 10) / 10 && checkpoints.length == 1) return 1
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
                    if (!(markerElement.classList.contains('checkpoint-marker-start') || markerElement.classList.contains('checkpoint-marker-goal') || markerElement.classList.contains('checkpoint-marker-startfinish'))) {
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

    async sortCheckpoints () {
        return new Promise(async (resolve, reject) => {
            var checkpoints = this.data.checkpoints
            const routeData = await this.getRouteData()
            const routeCoords = routeData.geometry.coordinates
            if (checkpoints) {
    
                // Get each point distance
                checkpoints.forEach( (checkpoint) => {
                    if (checkpoint.special == 'start') checkpoint.distance = 0 // If checkpoint has the same coordinates (at a 0.001 precision) as linestring first waypoint, then automatically set distance to 0
                    else if (checkpoint.special == 'goal') checkpoint.distance = turf.length(routeData)
                    else {
                        let point = turf.point([checkpoint.lngLat.lng, checkpoint.lngLat.lat])
                        let subline = turf.lineSlice(turf.point(routeCoords[0]), point, routeData)
                        checkpoint.distance = turf.length(subline)
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
            resolve(true)
        } )
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
            if (this.routeData) this.focus(this.routeData)
        }

        grabber.addEventListener('mousedown', mouseDownHandler)
    }

    /**
     * Display a highlighting layer at [lngLat] and before [previousLayerName]
     * @param {mapboxgl.LngLat} lngLat 
     * @param {string} previousLayerName 
     */
    setHighlightingLayer (lngLat, previousLayerName) {
        this.removeHighlightingLayer()
        this.map.addSource('highlight', {
            "type": "geojson",
            "data": {
                "type": "Feature",
                "geometry": {
                    "type": "Point",
                    "coordinates": [lngLat.lng, lngLat.lat]
                }
            }
        })
        this.map.addLayer({
            id: 'highlight',
            type: 'circle',
            source: 'highlight',
            paint: {
                'circle-color': '#ff5555',
                'circle-radius': 15,
                'circle-blur': 0.3
            }
        }, previousLayerName)
    }

    removeHighlightingLayer () {
        if (this.map.getLayer('highlight')) this.map.removeLayer('highlight')
        if (this.map.getSource('highlight')) this.map.removeSource('highlight')
    }
    
    updateMapDataListener = () => this.updateMapData()

    updateSceneries () {

        if (this.mapdata.sceneries && this.map.getZoom() > this.sceneriesZoomRoof) {

            const bounds = this.map.getBounds()
            const sceneries = this.mapdata.sceneries

            // Sort sceneries in popularity order
            sceneries.sort((a, b) => a.popularity - b.popularity)

            // First, remove all sceneries that have left bounds
            var collection = this.sceneriesMarkerCollection
            let i = 0
            while (i < collection.length) {
                // If existing marker is not inside new bounds OR should not be displayed at this zoom level
                if ((!(collection[i]._lngLat.lat < bounds._ne.lat && collection[i]._lngLat.lat > bounds._sw.lat) || !(collection[i]._lngLat.lng < bounds._ne.lng && collection[i]._lngLat.lng > bounds._sw.lng)) || !this.zoomPopularityFilter(collection[i].popularity)) {
                    // If existing scenery is not favoured
                    if (!collection[i].isFavorite) {
                        collection[i].remove() // Remove it from the DOM
                        collection.splice(i, 1) // Remove it from instance Nodelist
                        i--
                    }
                }
                i++
            }

            // Second, add all sceneries that have entered bounds
            let sceneriesSet = collection.length
            let keepSceneries = []
            let j = 0
            while (j < sceneries.length && sceneriesSet <= this.sceneriesMaxNumber) {
                // If scenery is inside bounds
                if ((sceneries[j].lat < bounds._ne.lat && sceneries[j].lat > bounds._sw.lat) && (sceneries[j].lng < bounds._ne.lng && sceneries[j].lng > bounds._sw.lng)) {
                    
                    // Verify it has not already been loaded
                    if (!document.querySelector('#scenery' + sceneries[j].id)) {
                        // Filter through zoom popularity algorithm
                        if (this.zoomPopularityFilter(sceneries[j].popularity) == true) {
                            this.setSceneryMarker(sceneries[j])
                            sceneriesSet++
                        } else keepSceneries.push(sceneries[j])
                    }
                }
                j++
            }

            // Third, if overall number of sceneries is still less than sceneriesMinNumber, add other sceneries inside bounds up to a total number of sceneriesMinNumber
            if (sceneriesSet < this.sceneriesMinNumber) {
                for (let sceneriesToSet = 0; sceneriesToSet < this.sceneriesMinNumber - sceneriesSet && sceneriesToSet < keepSceneries.length; sceneriesToSet++) {
                    this.setSceneryMarker(keepSceneries[sceneriesToSet])
                }
            }

            // Update sceneries scale
            document.querySelectorAll('.scenery-icon').forEach((sceneryIcon) => this.scaleMarkerAccordingToZoom(sceneryIcon))

        } else {
            for (let i = 0; i < this.sceneriesMarkerCollection.length; i++) {
                if (!this.sceneriesMarkerCollection[i].isFavorite) {
                    this.sceneriesMarkerCollection[i].remove()
                    this.sceneriesMarkerCollection.splice(i, 1)
                    i--
                }
            }
        }
    }

    async setSceneryMarker (scenery) {
        
        // Build element
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + scenery.thumbnail
        icon.classList.add('scenery-icon')
        if (scenery.isCleared) element.classList.add('visited-marker') // Highlight if visited
        if (scenery.isFavorite) element.classList.add('favoured-marker') // Highlight if favoured
        element.appendChild(icon)
        this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )
        marker.popularity = scenery.popularity // Append popularity data to the marker allowing popularity zoom filtering
        marker.isFavorite = scenery.isFavorite // Append favorites list data
        marker.setLngLat([scenery.lng, scenery.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'scenery' + scenery.id
        marker.getElement().classList.add('scenery-marker')
        marker.getElement().dataset.id = scenery.id
        marker.getElement().dataset.user_id = scenery.user_id
        this.sceneriesMarkerCollection.push(marker)

        // Build and attach popup
        var popupOptions = {
            closeOnMove: false
        }
        var instanceOptions = {}
        var instanceData = {
            mapInstance: this,
            scenery
        }
        let sceneryPopup = new SceneryPopup(popupOptions, instanceData, instanceOptions)
        marker.setPopup(sceneryPopup.popup)

        // Display scenery name on hover
        element.setAttribute('data-before', scenery.name)
        element.style.setProperty('--scenery-hover-display', 'none')
        element.addEventListener('mouseenter', () => element.style.setProperty('--scenery-hover-display', 'block'))
        element.addEventListener('mouseleave', () => element.style.setProperty('--scenery-hover-display', 'none'))
    }

    addFavoriteSceneries () {
        this.mapdata.sceneries.forEach( (scenery) => {
            // Verify it has not already been loaded
            if (!document.querySelector('#scenery' + scenery.id)) {
                if (scenery.isFavorite) this.setSceneryMarker(scenery)
            }
        } )
    }

    updateActivityPhotos () {

        if (this.map.getZoom() > this.activityPhotosZoomRoof) {

            const bounds = this.map.getBounds()

            // First, remove all activity photos that have left bounds
            var collection = this.activityPhotosMarkerCollection
            let i = 0
            while (i < collection.length) {
                // If existing marker is not inside new bounds OR should not be displayed at this zoom level
                if ((!(collection[i]._lngLat.lat < bounds._ne.lat && collection[i]._lngLat.lat > bounds._sw.lat) || !(collection[i]._lngLat.lng < bounds._ne.lng && collection[i]._lngLat.lng > bounds._sw.lng)) || this.map.getZoom() < this.activityPhotosZoomRoof) {
                    collection[i].remove() // Remove it from the DOM
                    collection.splice(i, 1) // Remove it from instance Nodelist
                    i--
                }
                i++
            }

            ajaxGetRequest ('/api/map.php?activity-photos=true&ne=' + bounds._ne.lng + ',' + bounds._ne.lat + '&sw='  + bounds._sw.lng + ',' + bounds._sw.lat, async (activityPhotos) => {
                
                // Second, add all activity photos that have entered bounds
                let activityPhotosSet = collection.length
                let keepActivityPhotos = []
                let j = 0
                while (j < activityPhotos.length) {
                    // If scenery is inside bounds
                    if ((activityPhotos[j].lngLat.lat < bounds._ne.lat && activityPhotos[j].lngLat.lat > bounds._sw.lat) && (activityPhotos[j].lngLat.lng < bounds._ne.lng && activityPhotos[j].lngLat.lng > bounds._sw.lng)) {
                        
                        // Verify it has not already been loaded
                        if (!document.querySelector('#activityPhoto' + activityPhotos[j].id)) {
                            // Filter zoom level
                            if (this.map.getZoom() > this.activityPhotosZoomRoof) {
                                this.setActivityPhotoMarker(activityPhotos[j])
                                activityPhotosSet++
                            } else keepActivityPhotos.push(activityPhotos[j])
                        }
                    }
                    j++
                }

            } )
            
            // Update markers scale
            document.querySelectorAll('.activity-photo-marker > *').forEach((ActivityPhotoIcon) => this.scaleActivityPhotoMarkerAccordingToZoom(ActivityPhotoIcon, {img: false}))

        } else {
            for (let i = 0; i < this.activityPhotosMarkerCollection.length; i++) {
                this.activityPhotosMarkerCollection[i].remove()
                this.activityPhotosMarkerCollection.splice(i, 1)
                i--
            }
        }
    }

    async setActivityPhotoMarker (activityPhoto) {
        
        // Build element
        let element = document.createElement('div')
        let icon = document.createElement('div')
        icon.classList.add('activity-photo-smallicon')
        element.appendChild(icon)
        this.scaleActivityPhotoMarkerAccordingToZoom(icon, {img: false}) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )
        marker.setLngLat([activityPhoto.lngLat.lng, activityPhoto.lngLat.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'activityPhoto' + activityPhoto.id
        marker.getElement().classList.add('activity-photo-marker')
        marker.getElement().dataset.id = activityPhoto.id
        marker.getElement().dataset.user_id = activityPhoto.user_id
        this.activityPhotosMarkerCollection.push(marker)

        // Build and attach popup
        var popupOptions = {
            closeOnMove: false
        }
        var instanceOptions = {}
        var instanceData = {
            activityPhoto
        }
        let activityPhotoPopup = new ActivityPhotoPopup(popupOptions, instanceData, instanceOptions)
        marker.setPopup(activityPhotoPopup.popup)
    }

    zoomPopularityFilter (popularity) {

        const zoom = this.map.getZoom()

        // Define zoom levels
        const fullDisplayZone  = 6 // Range of zoom levels starting maxZoomLevel which all sceneries will be displayed
        const maxZoomLevel     = 22 // Maximum zoom level of map provider (22 for Mapbox)
        const zoomLevel0       = maxZoomLevel - fullDisplayZone // Zoom level from which all sceneries will be displayed
        const zoomRange        = zoomLevel0 - this.sceneriesZoomRoof
        if (zoomRange <= 0) return true // zoomRange can't be negative (don't filter anything in this case)
        const zoomStep = zoomRange / 4
        var zoomLevel1 = zoomLevel0 - zoomStep
        var zoomLevel2 = zoomLevel1 - zoomStep
        var zoomLevel3 = zoomLevel2 - zoomStep
        var zoomLevel4 = this.sceneriesZoomRoof

        // Define popularity levels
        var popularityLevel4 = 110
        var popularityLevel3 = 60
        var popularityLevel2 = 30
        var popularityLevel1 = 0

        // Over upper limit
        if (zoom < this.sceneriesZoomRoof) {
            return false
        }

        // Level 4
        else if (zoom > zoomLevel4 && zoom < zoomLevel3) {
            if (popularity > popularityLevel4) {
                return true
            } else {
                return false
            }
        }

        // Level 3
        else if (zoom > zoomLevel3 && zoom < zoomLevel2) {
            if (popularity > popularityLevel3) {
                return true
            } else {
                return false
            }
        }
        
        // Level 2
        else if (zoom > zoomLevel2 && zoom < zoomLevel1) {
            if (popularity > popularityLevel2) {
                return true
            } else {
                return false
            }
        }

        // Level 1
        else if (zoom > zoomLevel1 && zoom < zoomLevel0) {
            if (popularity > popularityLevel1) {
                return true
            } else {
                return false
            }
        }

        // Down lower limit
        else {
            return true
        }

    }
}