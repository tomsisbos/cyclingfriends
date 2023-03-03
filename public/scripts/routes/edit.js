import EditRouteHelper from "/scripts/helpers/routes/edit.js"
import CFUtils from "/map/class/CFUtils.js"
import EditRouteMap from "/map/class/route/EditRouteMap.js"

var editRouteMap = new EditRouteMap()

// Set default layer according to current season
var map = await editRouteMap.load(document.getElementById('EditRouteMap'), 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

// Controls
editRouteMap.addStyleControl()
editRouteMap.addRouteControl()
editRouteMap.addRouteEditionControl()
editRouteMap.addOptionsControl()

// Layers
editRouteMap.addSources()
editRouteMap.addAmenityLayers()
editRouteMap.addKonbiniLayers()
editRouteMap.addCyclingLayers()

const canvas = map.getCanvasContainer()

/* -- Route initialization -- */

// Get and display route information from the server
ajaxGetRequest (editRouteMap.apiUrl + '?route-load=' + editRouteMap.routeId, async (response) => {
    editRouteMap.routeData = response
    // Add route layer
    var coordinates = []
    editRouteMap.routeData.coordinates.forEach( (coordinate) => {
        coordinates.push([parseFloat(coordinate.lng), parseFloat(coordinate.lat)])
    } )
    var geojson = {
        type: 'Feature',
        properties: {
            tunnels: editRouteMap.routeData.tunnels
        },
        geometry: {
            type: 'LineString',
            coordinates: coordinates
        }
    }
    editRouteMap.addRouteLayer(geojson)
    // Add startpoint layer
    map.addLayer( {
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
                        coordinates: coordinates[0]
                    }
                } ]
            }
        },
        paint: {
            'circle-radius': 8,
            'circle-color': '#afffaa',
            'circle-stroke-color': 'blue',
            'circle-stroke-width': 2
        }
    } )
    editRouteMap.configureStartPoint()
    editRouteMap.start = coordinates[0]
    // Add endpoint layer
    map.addLayer({
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
                        coordinates: coordinates[coordinates.length - 1]
                    }
                } ]
            }
        },
        paint: {
        'circle-radius': 8,
        'circle-color': '#ff5555',
        'circle-stroke-color': 'blue',
        'circle-stroke-width': 2
        }
    } )
    editRouteMap.configureEndPoint()

    editRouteMap.paintTunnels(editRouteMap.routeData.tunnels)
    editRouteMap.updateDistanceMarkers()
    var routeBounds = CFUtils.defineRouteBounds(coordinates)
    map.fitBounds(routeBounds)
    editRouteMap.waypointNumber = 1
    editRouteMap.addState()
} )

// Set edition mode by default
editRouteMap.modeSelect.value = 'addWaypoints'

// Init route building
editRouteMap.setMode()

// Display start guidance window
await EditRouteHelper.startGuidance()

// Profile container
var profileCanvasContainer = document.createElement('div')
profileCanvasContainer.className = 'd-flex profile-inside-map'
profileCanvasContainer.id = 'profileBox'
document.querySelector('body').appendChild(profileCanvasContainer)
// Profile tag button
var profileTag = document.createElement('div')
profileTag.className = 'map-profile-tag cursor-pointer'
profileTag.innerText = 'Show profile ▲'
profileCanvasContainer.appendChild(profileTag)
// Profile canvas element
var profileCanvasElement = document.createElement('canvas')
profileCanvasElement.style.height = '0px'
profileCanvasElement.id = 'elevationProfile'
profileCanvasContainer.appendChild(profileCanvasElement)

// Toggle profile on click on profile tag (only if elevation data is available (= 2 points or more))
profileTag.addEventListener('click', () => {
    if (map.getSource('endPoint')) {
        editRouteMap.toggleProfile()
    }
} )

// Declare canvas variable

map.once('idle', () => {

    // Set cursor options corresponding to edit mode
    canvas.style.cursor = 'grab'
    map.on('mouseenter', 'route', () => {
        canvas.style.cursor = 'crosshair'
    } )
    map.on('mouseleave', 'route', () => {
        if (boxAddWaypoints.checked) {
            canvas.style.cursor = 'grab'
        }
    } )

    // When route is updated and loaded
    map.on('sourcedata', async (e) => {
        if (e.sourceId == 'route' && (e.sourceDataType == 'content' || e.sourceDataType == 'metadata')) { // On a source data change different from visibility (ex.: tiles unloading on move)
            editRouteMap.generateProfile()
            editRouteMap.updateDistanceMarkers()
        }
    } )

    // When zoomed
    map.on('zoomend', () => {
        editRouteMap.updateDistanceMarkers()
    } )

} )