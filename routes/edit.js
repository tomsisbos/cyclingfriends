import CFUtils from "/map/class/CFUtils.js"
import EditRouteMap from "/map/class/EditRouteMap.js"

var editRouteMap = new EditRouteMap ()

// Set default layer according to current season
var map = await editRouteMap.load(document.getElementById('EditRouteMap'), 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

editRouteMap.addSources()
editRouteMap.addAmenityLayers()
editRouteMap.addKonbiniLayers()
editRouteMap.addCyclingLayers()

const canvas = map.getCanvasContainer()


/* -- Controls -- */

editRouteMap.addOptionsControl()
editRouteMap.addBuildRouteControl()


/*

const canvas = map.getCanvasContainer()


// -- Controls -- 


// Controller
var controller = document.createElement('div')
controller.className = 'map-controller map-controller-left flex-column-reverse'
editRouteMap.$map.appendChild(controller)
// Container
var routeContainer = document.createElement('div')
routeContainer.className = 'map-controller-block fullwidth flex-column'
controller.appendChild(routeContainer)
// Line 1
let line1 = document.createElement('div')
line1.className = 'map-controller-line'
routeContainer.appendChild(line1)
var boxFollowRoads = document.createElement('input')
boxFollowRoads.id = 'boxFollowRoads'
boxFollowRoads.setAttribute('type', 'checkbox')
boxFollowRoads.setAttribute('checked', 'checked')
line1.appendChild(boxFollowRoads)
var boxFollowRoadsLabel = document.createElement('div')
boxFollowRoadsLabel.innerText = 'Follow roads'
line1.appendChild(boxFollowRoadsLabel)
// Line 2
let line2 = document.createElement('div')
line2.className = 'map-controller-line'
routeContainer.appendChild(line2)
var boxAddWaypoints = document.createElement('input')
boxAddWaypoints.setAttribute('type', 'checkbox')
boxAddWaypoints.setAttribute('checked', 'checked') // Checked by default in edit mode
boxAddWaypoints.id = "boxAddWaypoints"
line2.appendChild(boxAddWaypoints)
var boxAddWaypointsLabel = document.createElement('div')
boxAddWaypointsLabel.innerText = 'Add waypoints mid-way'
line2.appendChild(boxAddWaypointsLabel)
boxAddWaypoints.addEventListener('change', () => {
    editRouteMap.setMode()
} )

// Line 3
let line3 = document.createElement('div')
line3.className = 'map-controller-line'
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
    editRouteMap.updateDistanceMarkers()
} )
// Line 4
let line4 = document.createElement('div')
line4.className = 'map-controller-line'
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
        map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 1})
    } else {
        map.setTerrain({'source': 'mapbox-dem', 'exaggeration': 0})
    }
} )
// Camera buttons
let line5 = document.createElement('div')
line5.className = 'map-controller-line'
routeContainer.appendChild(line5)
// Focus button
var buttonFocus = document.createElement('button')
buttonFocus.className = 'map-controller-block mp-button mp-button-small'
buttonFocus.id = 'buttonFocus'
buttonFocus.innerText = 'Focus'
line5.appendChild(buttonFocus)
buttonFocus.addEventListener('click', () => {
    editRouteMap.focus(map.getSource('route'))
} )
// Fly button
var buttonFly = document.createElement('button')
buttonFly.className = 'map-controller-block mp-button mp-button-small'
buttonFly.id = 'buttonFly'
buttonFly.innerText = 'Fly'
line5.appendChild(buttonFly)
buttonFly.addEventListener('click', () => {
    if (map.getSource('route')) {
        editRouteMap.flyAlong(map.getSource('route'))
    }
} )
// Edition buttons
let line6 = document.createElement('div')
line6.className = 'map-controller-line'
routeContainer.appendChild(line6)
// Clear button
var buttonClear = document.createElement('button')
buttonClear.className = 'map-controller-block mp-button mp-button-small'
buttonClear.id = 'buttonClear'
buttonClear.innerText = 'Clear'
line6.appendChild(buttonClear)
buttonClear.addEventListener('click', () => {
    editRouteMap.clearRoute()
    editRouteMap.hideProfile()
} )
// Save button
var buttonSave = document.createElement('button')
buttonSave.className = 'map-controller-block mp-button mp-button-small'
buttonSave.id = 'buttonSave'
buttonSave.innerText = 'Save'
line6.appendChild(buttonSave)
buttonSave.addEventListener('click', async () => {
    // Hide waypoints
    let i = 2
    while (map.getSource('wayPoint' + i)) {
        map.setLayoutProperty('wayPoint' + i, 'visibility', 'none')
        i++
    }
    // Center camera
    var routeBounds = CFUtils.defineRouteBounds(map.getSource('route')._data.geometry.coordinates)
    map.fitBounds(routeBounds)
    // Open save popup
    var answer = await editRouteMap.openSavePopup()
    if (answer) {
        // Save canvas as a picture
        html2canvas(document.querySelector('.mapboxgl-canvas'), {windowWidth: 1800, windowHeight: 960, width: 1100, height: 640, x: 150}).then( (canvas) => {
            canvas.toBlob( async (blob) => {
                answer.thumbnail = await blobToBase64(blob)
                console.log(answer)
                // When treatment is done, redirect to my routes page
                editRouteMap.saveRoute(answer)
            }, 'image/jpeg', 0.7)
        } )       
    } else {
        // Restore waypoints
        let i = 2
        while (map.getSource('wayPoint' + i)) {
            map.setLayoutProperty('wayPoint' + i, 'visibility', 'visible')
            i++
        }
    }
} )

// On map style change
map.on('styledata', (e) => {
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

// Map style
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
    editRouteMap.setMapStyle(layerId)
    editRouteMap.generateProfile()
}

// Controls
map.addControl(
    new mapboxgl.GeolocateControl( {
        positionOptions: {
            enableHighAccuracy: true
        }
    } )
)
map.addControl(
    new mapboxgl.ScaleControl( {
        maxWidth: 80,
        unit: 'metric'
    } )
)*/


/* -- Route initialization -- */


// Get and display route information from the server
ajaxGetRequest (editRouteMap.apiUrl + '?route-load=' + editRouteMap.routeId, async (response) => {
    editRouteMap.routeData = response
    console.log(response)
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
} )


/* -- Route building -- */

editRouteMap.setMode()


/* -- Profile drawing -- 
Use Chart.js
*/


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