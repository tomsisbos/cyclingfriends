import CFUtils from "/map/class/CFUtils.js"
import BuildRouteMap from "/map/class/BuildRouteMap.js"

var buildRouteMap = new BuildRouteMap ()

var map = await buildRouteMap.load(document.getElementById('BuildRouteMap'), 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
buildRouteMap.addSources()
buildRouteMap.addAmenityLayers()
buildRouteMap.addKonbiniLayers()

const canvas = map.getCanvasContainer()


/* -- Controls -- */

buildRouteMap.addOptionsControl()
buildRouteMap.addBuildRouteControl()

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

buildRouteMap.addStyleControl()

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
)


/* -- Route building -- */

buildRouteMap.setMode()


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
profileTag.innerText = 'No elevation data to display.'
profileCanvasContainer.appendChild(profileTag)
// Profile canvas element
var profileCanvasElement = document.createElement('canvas')
profileCanvasElement.style.height = '0px'
profileCanvasElement.id = 'elevationProfile'
profileCanvasContainer.appendChild(profileCanvasElement)

// Toggle profile on click on profile tag (only if elevation data is available (= 2 points or more))
profileTag.addEventListener('click', () => {
    if (map.getSource('endPoint')) {
        buildRouteMap.toggleProfile()
    }
} )

// Declare canvas variable

map.once('idle', () => {

    canvas.style.cursor = 'crosshair'

    // When route is updated and loaded
    map.on('sourcedata', async (e) => {
        if (e.sourceId == 'route' && (e.sourceDataType == 'content' || e.sourceDataType == 'metadata')) { // On a source data change different from visibility (ex.: tiles unloading on move)
            buildRouteMap.generateProfile()
            buildRouteMap.updateDistanceMarkers()
        }
    } )

    // When zoomed
    map.on('zoomend', () => {
        buildRouteMap.updateDistanceMarkers()
    } )

} )