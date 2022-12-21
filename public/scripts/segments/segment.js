import CFUtils from "/map/class/CFUtils.js"
import Modal from "/map/class/Modal.js"
import SegmentMap from "/map/class/SegmentMap.js"
import Polyline from '/node_modules/@mapbox/polyline/index.js'

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
    
    // Set map instance paint property data
    if (segment.rank == 'local') segmentMap.routeColor = segmentMap.segmentLocalColor
    else if (segment.rank == 'regional') segmentMap.routeColor = segmentMap.segmentRegionalColor
    else if (segment.rank == 'national') segmentMap.routeColor = segmentMap.segmentNationalColor

    // On click on a thumbnail, open modal
    document.querySelectorAll('.pg-sg-photo img').forEach( (img) => {
        var modal = new Modal(img.src)
        img.after(modal.element)
        img.addEventListener('click', () => modal.open())
    } )

    // If map is interactive
    if ($map.getAttribute('interactive') == 'true') {
    
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

    // If map is static
    } else {

        // Hide profile element
        document.querySelector('#profileBox').style.display = 'none'

        // Build seasons layer
        var colors = segmentMap.getSeasonalColors(segmentMap.season)
        var seasonData = {
            'id': 'landcover-season',
            'type': 'fill',
            'source': 'composite',
            'source-layer': 'landcover',
            'paint': {
                'fill-color': colors,
                'fill-opacity': 0.2
            }
        }
        var seasonLayer = encodeURIComponent(JSON.stringify(seasonData))
        
        // Build route overlay
        const routeData = segmentMap.data.routeData
        const routeCoordinates = routeData.geometry.coordinates
        var staticRouteData = turf.simplify(routeData, {tolerance: 0.0005, highQuality: true})
        var revertedCoordinates = staticRouteData.geometry.coordinates.map(coordinate => {
            return [coordinate[1], coordinate[0]]
        })
        var staticRoutePolyline = Polyline.encode(revertedCoordinates)
        var staticRoutePolylineUri = encodeURIComponent(staticRoutePolyline)

        // Build bounding box
        var routeBounds = CFUtils.defineRouteBounds(staticRouteData.geometry.coordinates)
        var boundingBox = [routeBounds[0][0], routeBounds[0][1], routeBounds[1][0], routeBounds[1][1]]
        var boundingBoxUri = JSON.stringify(boundingBox)

        // Build mkpoints
        segmentMap.mkpoints = await segmentMap.loadCloseMkpoints(2, {displayOnMap: false, generateProfile: false, getFileBlob: false})
        const markerColor = 'fff'
        var mkpoints = ''
        segmentMap.mkpoints.forEach(mkpoint => {
            console.log(mkpoint)
            if (mkpoint.on_route) mkpoints += ',pin-s-' + Math.round(mkpoint.distance) + '+' + markerColor + '('  + mkpoint.lngLat.lng + ',' + mkpoint.lngLat.lat + ')'
        } )

        // Set size
        if (window.innerWidth < 1280) var width = window.innerWidth
        else {
            var width = 1280
            $map.parentElement.style.height = Math.round(450 + ((window.innerWidth - 1200) * 0.275))
        }

        // Build api request
        var url = `
https://api.mapbox.com/
styles/v1/sisbos/cl07xga7c002616qcbxymnn5z/
static/
path-` + (segmentMap.routeWidth + 4) + `+` + segmentMap.segmentCapColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
path-` + segmentMap.routeWidth + `+` + segmentMap.routeColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
url-` + encodeURIComponent('https://img.icons8.com/flat-round/64/stop.png') + `(` + routeCoordinates[routeCoordinates.length - 1][0] + `,` + routeCoordinates[routeCoordinates.length - 1][1] + `),
url-` + encodeURIComponent('https://img.icons8.com/flat-round/64/play.png') + `(` + routeCoordinates[0][0] + `,` + routeCoordinates[0][1] + `)
` + mkpoints + `/
` + boundingBoxUri + `/
` + width + `x450@2x
?padding=99
&addlayer=` + seasonLayer + `
&before_layer=landuse
&access_token=pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdndjY1bTI4OTUzZG5wOGs5ZWVsNnUifQ.2BcHCFVvk0SWQOb5PejCdQ
`

console.log(url.length)

        // Display static map inside container
        $map.querySelector('img').src = url

    }

} )