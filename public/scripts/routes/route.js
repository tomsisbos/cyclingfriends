import CFUtils from "/map/class/CFUtils.js"
import RoutePageMap from "/map/class/RoutePageMap.js"

var routePageMap = new RoutePageMap()

console.log(routePageMap)

var $map = document.getElementById('routePageMap')

const exportButton = document.querySelector('#export')

// Get route data from server
if (routePageMap.routeId) var queryString = "?route-load=" + routePageMap.routeId
else var queryString = "?route-load-from-ride=" + routePageMap.rideId
ajaxGetRequest (routePageMap.apiUrl + queryString, async (route) => {
    
    routePageMap.routeId = route.id
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

    exportButton.href = CFUtils.loadGpx(geojson)
    exportButton.download = route.name + '.gpx'

    // Populate instance route property
    routePageMap.data = route
    routePageMap.data.routeData = geojson
    
    // Set default layer according to current season
    var map = await routePageMap.load($map, routePageMap.defaultStyle, coordinates[0])
    
    // Set grabber 
    routePageMap.setGrabber()

    // Display CF Layers
    routePageMap.addSources()
    routePageMap.addLayers()

    /* -- Controls -- */

    routePageMap.addStyleControl()
    routePageMap.addRouteControl()

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

    var fittingSegments = await routePageMap.getFittingSegments()
    console.log(fittingSegments)
    fittingSegments.forEach( (segment) => {
        routePageMap.displaySegment(segment)
    } )
    
} )