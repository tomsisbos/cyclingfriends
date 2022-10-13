import CFUtils from "/map/class/CFUtils.js"
import RoutePageMap from "/map/class/RoutePageMap.js"

// Button handlers
if (document.getElementById('deleteRoute')) {
    document.getElementById('deleteRoute').addEventListener('click', async () => {
        var answer = await openConfirmationPopup('Do you really want to delete this route ?')
        if (answer) {
            ajaxGetRequest (routePageMap.apiUrl + "?route-delete=" + routePageMap.routeId, async (response) => {
                console.log(response)
                window.location.replace('/map/routes.php')
            } )
        }
    } )
}

var routePageMap = new RoutePageMap()

console.log(routePageMap)

var $map = document.getElementById('routePageMap')
const exportButton = document.querySelector('#export')

// Get route data from server
ajaxGetRequest (routePageMap.apiUrl + "?route-load=" + routePageMap.routeId, async (route) => {
    
    var coordinates = []
    route.coordinates.forEach( (coordinate) => {
        coordinates.push([parseFloat(coordinate.lng), parseFloat(coordinate.lat)])
    } )
    
    // Build route geojson
    const geojson = {
        type: 'Feature',
        properties: {
            tunnels: route.tunnels
        },
        geometry: {
            type: 'LineString',
            coordinates: coordinates
        }
    }

    // Populate instance route property
    routePageMap.data = route
    routePageMap.data.routeData = geojson

    // Load gpx file
    exportButton.href = CFUtils.loadGpx(geojson)
    exportButton.download = route.name + '.gpx'
    
    // Set default layer according to current season
    var map = await routePageMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z', coordinates[0])
    
    // Set grabber 
    routePageMap.setGrabber()

    // Display CF Layers
    routePageMap.addSources()
    routePageMap.addLayers()

    /* -- Controls -- */

    routePageMap.addRouteControl()

    /*
    // Controller
    var controller = document.createElement('div')
    controller.className = 'map-controller map-controller-left flex-column-reverse'
    $map.appendChild(controller)
    
    // Container
    var routeContainer = document.createElement('div')
    routeContainer.className = 'map-controller-block fullwidth flex-column'
    controller.appendChild(routeContainer)
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
        routePageMap.updateDistanceMarkers()
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
    // Line 5
    let line5 = document.createElement('div')
    line5.className = 'map-controller-line'
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
            routePageMap.addMkpoints(routePageMap.mkpoints)
            document.querySelector('.rt-slider').style.display = 'flex'
            routePageMap.generateProfile()
        } else {
            routePageMap.hideMkpoints()
            document.querySelector('.rt-slider').style.display = 'none'
            routePageMap.generateProfile()
        }
    } )
    // Camera buttons
    let line6 = document.createElement('div')
    line6.className = 'map-controller-line'
    routeContainer.appendChild(line6)
    // Focus button
    var buttonFocus = document.createElement('button')
    buttonFocus.className = 'map-controller-block mp-button mp-button-small'
    buttonFocus.id = 'buttonFocus'
    buttonFocus.innerText = 'Focus'
    line6.appendChild(buttonFocus)
    buttonFocus.addEventListener('click', () => {
        routePageMap.focus(map.getSource('route')._data)
    } )
    // Fly button
    var buttonFly = document.createElement('button')
    buttonFly.className = 'map-controller-block mp-button mp-button-small'
    buttonFly.id = 'buttonFly'
    buttonFly.innerText = 'Fly'
    line6.appendChild(buttonFly)
    buttonFly.addEventListener('click', () => {
        if (map.getSource('route')) {
            routePageMap.flyAlong(map.getSource('route')._data)
        }
    } )
    // Edition buttons
    let line7 = document.createElement('div')
    line7.className = 'map-controller-line'
    routeContainer.appendChild(line7)
    */

    /*
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
        routePageMap.setMapStyle(layerId)
    }*/

    routePageMap.addStyleControl()

    // Controls
    document.querySelector('.mapboxgl-ctrl-logo').style.display = 'none'
    map.addControl(
        new mapboxgl.GeolocateControl( {
            positionOptions: {
                enableHighAccuracy: true
            }
        } )
    )

    // Set map instance paint property data
    if (routePageMap.rideId) routePageMap.routeColor = 'yellow'
    
    // Display route
    routePageMap.addRouteLayer(geojson)
    
    // Generate profile on idle
    map.once('idle', () => routePageMap.generateProfile())
    
    // Focus
    var routeBounds = CFUtils.defineRouteBounds(coordinates)
    map.fitBounds(routeBounds)
    
    // Paint route properties
    routePageMap.paintTunnels(route.tunnels)
    routePageMap.updateDistanceMarkers()
    if (!routePageMap.rideId) routePageMap.displayStartGoalMarkers(geojson)

    // Request and display mkpoints close to the route
    routePageMap.loadCloseMkpoints(2).then( async () => {

        // If ride ID is found insite query string parameters, get ride data from server
        if (routePageMap.rideId) await routePageMap.loadRide()
            
        // Build route specs table
        routePageMap.buildSlider()
        routePageMap.buildTable()
    } )
} )