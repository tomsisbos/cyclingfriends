import Env from "../Env.js"
import mapboxgl from 'mapbox-gl'
import Model from './Model.js'

export default class MapWrapper extends Model {

    map
    season
    month = new Date().getMonth() + 1
    
    constructor (initializationOptions) {
        super()

        this.map = new mapboxgl.Map({
            accessToken: Env.mapboxApiKey,
            style: 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z',
            ...initializationOptions
        })
        
        this.setSeason()
        
        this.map.once('load', () => {
            this.styleSeason()
            this.loadTerrain()
            this.loadImages()
            this.addSources()
            this.addLayers()
        } )
    }

    unmount = () => this.map.remove()

    /**
     * Change map style depending on current season
     */
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

    /**
     * Sets the way seasons are styled
     * @param {String} season The season name
     * @returns A mapbox expression
     */
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

    /**
     * Add media
     */
    async loadImages () {
        if (!this.map.hasImage('leader-line-white')) {
            this.map.loadImage('/map/media/leader-line-white.png', (error, image) => {
                if (error) throw error
                this.map.addImage('leader-line-white', image)
            } )
        }
        var amenityIcons = ['toilets', 'water', 'vending-machine', 'seven-eleven', 'family-mart', 'lawson', 'mini-stop', 'daily-yamazaki', 'michi-no-eki', 'onsen', 'footbath', 'bicycle-rentals']
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

    /**
     * Load elevation data
     */
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
        // Cycling Shops
        this.map.addSource('bicycle-rentals', {
            'type': 'geojson',
            'data': '/map/sources/bicycle-rentals.geojson',
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
        // Cycle shops
        this.addCycleShopLayers()
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

    addCycleShopLayers () {
        // Bicycle rentals
        this.map.addLayer( {
            'id': 'bicycle-rentals',
            'type': 'symbol',
            'source': 'bicycle-rentals',
            'minzoom': 12,
            'layout': {
                'icon-image': '_icon-bicycle-rentals',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    12,
                    0.45,
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
    }
    
    hideCycleShopLayers () {
        var cycleShopLayerNames = ['bicycle_rental']
        cycleShopLayerNames.forEach( (layerName) => this.map.removeLayer(layerName))
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
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-seven-eleven',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    11.5,
                    0.6,
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
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-family-mart',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    11.5,
                    0.6,
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
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-family-mart',
                'icon-size': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    11.5,
                    0.6,
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
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-lawson',
                'icon-size': [
                    "interpolate",
                    ["linear"],
                    ["zoom"],
                    11.5,
                    0.6,
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
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-mini-stop',
                'icon-size': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    11.5,
                    0.6,
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
            'minzoom': 11.5,
            'layout': {
                'icon-image': '_icon-daily-yamazaki',
                'icon-size': [
                    'interpolate',
                    ['linear'],
                    ['zoom'],
                    11.5,
                    0.6,
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
            'minzoom': 11,
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
            'id': 'no-bicycle-cap',
            'type': 'line',
            'source': 'no-bicycle',
            'source-layer': 'no-bicycle-2drz45',
            'minzoom': 11,
            'paint': {
                'line-color': '#fff',
                'line-width': 5,
                'line-color': '#ff5555'
            },
            'filter': ['in', 'id', 'default']
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
}