import CFUtils from "/map/class/CFUtils.js"
import RoutePageMap from "/map/class/RoutePageMap.js"

// Button handlers
if (document.getElementById('deleteRoute')) {
    document.getElementById('deleteRoute').addEventListener('click', async () => {
        var answer = await openConfirmationPopup('Do you really want to delete this route ?')
        if (answer) {
            ajaxGetRequest (routePageMap.apiUrl + "?route-delete=" + routePageMap.routeId, async (response) => {
                console.log(response)
                window.location.replace('/routes.php')
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
    routePageMap.loadCloseMkpoints(2).then( async (mkpoints) => {

        // Load mkpoints into map instance
        routePageMap.mkpoints = mkpoints

        // If ride ID is found insite query string parameters, get ride data from server
        if (routePageMap.rideId) await routePageMap.loadRide()
            
        // Build route specs table
        routePageMap.buildSlider()
        routePageMap.buildTable()
    } )
} )