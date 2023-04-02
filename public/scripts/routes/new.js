import BuildRouteMap from "/map/class/route/BuildRouteMap.js"

var buildRouteMap = new BuildRouteMap ()

var map = await buildRouteMap.load(document.getElementById('BuildRouteMap'), 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

// Controls
buildRouteMap.addStyleControl()
buildRouteMap.addRouteControl()
buildRouteMap.addRouteEditionControl()
buildRouteMap.addOptionsControl()

// Layers
buildRouteMap.addSources()
buildRouteMap.addAmenityLayers()
buildRouteMap.addKonbiniLayers()
buildRouteMap.addCyclingLayers()

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


// Profile container
var profileCanvasContainer = document.createElement('div')
profileCanvasContainer.className = 'd-flex profile-inside-map'
profileCanvasContainer.id = 'profileBox'
document.querySelector('body').appendChild(profileCanvasContainer)
// Profile tag button
var profileTag = document.createElement('div')
profileTag.className = 'map-profile-tag cursor-pointer'
profileTag.innerText = '表示できる標高データはありません。'
profileCanvasContainer.appendChild(profileTag)
// Profile canvas element
var profileCanvasElement = document.createElement('canvas')
profileCanvasElement.style.height = '0px'
profileCanvasElement.id = 'elevationProfile'
profileCanvasContainer.appendChild(profileCanvasElement)

// Toggle profile on click on profile tag (only if elevation data is available (= 2 points or more))
profileTag.addEventListener('click', () => {
    if (map.getSource('endPoint')) buildRouteMap.toggleProfile()
} )

// Declare canvas variable

map.once('idle', () => {

    map.getCanvas().style.cursor = 'crosshair'

    // When route is updated and loaded
    map.on('sourcedata', async (e) => {
        if (e.sourceId == 'route' && (e.sourceDataType == 'content' || e.sourceDataType == 'metadata')) { // On a source data change different from visibility (ex.: tiles unloading on move)
            buildRouteMap.profile.clearData()
            buildRouteMap.profile.generate({precise: false})
            buildRouteMap.updateDistanceMarkers()
        }
    } )

    // When zoomed
    map.on('zoomend', () => {
        buildRouteMap.updateDistanceMarkers()
    } )

} )