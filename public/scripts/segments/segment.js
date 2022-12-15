import CFUtils from "/map/class/CFUtils.js"
import SegmentMap from "/map/class/SegmentMap.js"

var segmentMap = new SegmentMap()

console.log(segmentMap)

var $map = document.getElementById('segmentMap')
const exportButton = document.querySelector('#export')

// Set timeline container height
var timelineContainer = document.querySelector('.pg-sg-season-descriptions')
if (timelineContainer) timelineContainer.style.height = (timelineContainer.querySelector('p').offsetHeight) + 'px'

// Get route data from server
ajaxGetRequest (segmentMap.apiUrl + "?segment-load=" + segmentMap.segmentId, async (segment) => {
    
    var coordinates = []
    segment.route.coordinates.forEach( (coordinate) => {
        coordinates.push([parseFloat(coordinate.lng), parseFloat(coordinate.lat)])
    } )
    
    // Build route geojson
    const geojson = {
        type: 'Feature',
        properties: {
            tunnels: segment.route.tunnels
        },
        geometry: {
            type: 'LineString',
            coordinates: coordinates
        }
    }

    // Populate instance route property
    segmentMap.segment = segment
    segmentMap.data = segment.route
    segmentMap.data.routeData = geojson

    // Load gpx file
    exportButton.href = CFUtils.loadGpx(geojson)
    exportButton.download = segment.route.name + '.gpx'
    
    // Set default layer according to current season
    var map = await segmentMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z', coordinates[0])

    // Display CF Layers
    segmentMap.addSources()
    segmentMap.addLayers()

    // Controls
    segmentMap.addStyleControl()
    segmentMap.addRouteControl()
    segmentMap.addFullscreenControl()
    document.querySelector('.mapboxgl-ctrl-logo').style.display = 'none'
    map.addControl(
        new mapboxgl.GeolocateControl( {
            positionOptions: {
                enableHighAccuracy: true
            }
        } )
    )

    // Set map instance paint property data
    if (segment.rank == 'local') segmentMap.routeColor = segmentMap.segmentLocalColor
    else if (segment.rank == 'regional') segmentMap.routeColor = segmentMap.segmentRegionalColor
    else if (segment.rank == 'national') segmentMap.routeColor = segmentMap.segmentNationalColor
    
    // Display route
    segmentMap.addRouteLayer(geojson)
    
    // Generate profile on idle
    map.once('idle', () => segmentMap.generateProfile())
    
    // Focus
    var routeBounds = CFUtils.defineRouteBounds(coordinates)
    map.fitBounds(routeBounds)
    
    // Paint route properties
    segmentMap.paintTunnels(segment.route.tunnels)
    segmentMap.updateDistanceMarkers()
    segmentMap.displayStartGoalMarkers(geojson)

    // Request and display mkpoints close to the route
    segmentMap.loadCloseMkpoints(5).then( async (mkpoints) => {

        // Load mkpoints into map instance
        segmentMap.mkpoints = mkpoints
    } )
} )