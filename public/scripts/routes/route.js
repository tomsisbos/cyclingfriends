import CFUtils from "/map/class/CFUtils.js"
import RoutePageMap from "/map/class/RoutePageMap.js"
import Polyline from '/node_modules/@mapbox/polyline/index.js'
console.log(Polyline)

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

    // If map is interactive
    if ($map.dataset.interactive == 'true') {
    
        // Set default layer according to current season
        var map = await routePageMap.load($map, routePageMap.defaultStyle, coordinates[0])
        
        // Set grabber 
        routePageMap.setGrabber()

        // Display CF Layers
        routePageMap.addSources()
        routePageMap.addLayers()

        // Controls
        routePageMap.addStyleControl()
        routePageMap.addRouteControl()

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
            
            // If ride ID is found inside query string parameters, get ride data from server
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

    // If map is static
    } else {

        // Hide profile element

        document.querySelector('#profileBox').style.display = 'none'

        const routeData = routePageMap.data.routeData
        const routeCoordinates = routeData.geometry.coordinates

        // Build seasons layer
        var colors = routePageMap.getSeasonalColors(routePageMap.season)
        console.log(colors)
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

        // Build checkpoints
        var checkpoints = ''
        if (routePageMap.rideId) {
            routePageMap.ride = await getRide()
            
            var checkpointsCoordinates = []
            routePageMap.ride.checkpoints.forEach(checkpoint => {
                if (!checkpoint.special.length > 0) checkpointsCoordinates.push([checkpoint.lngLat.lng, checkpoint.lngLat.lat]) // remove start and goal markers
            } )
            var checkpointsData = {
                "type": "Feature",
                "properties": {
                    "marker-size": "s",
                    "marker-symbol": "circle",
                    "marker-color": "#0000FF"
                },
                "geometry": {
                    "type": "MultiPoint",
                    "coordinates": checkpointsCoordinates
                }
            }
            checkpoints = ',geojson(' + encodeURIComponent(JSON.stringify(checkpointsData)) + ')'
        }

        // Request and display mkpoints close to the route
        routePageMap.loadCloseMkpoints(2, {displayOnMap: false, generateProfile: false, getFileBlob: false}).then( async (mkpoints) => {
            routePageMap.mkpoints = mkpoints
            routePageMap.buildTable()
        } )


        // Set size
        if (window.innerWidth < 1280) {
            var width = window.innerWidth
        } else {
            var width = 1280
            $map.parentElement.style.height = Math.round(450 + ((window.innerWidth - 1200) * 0.275))
        }

        // Build api request
        var url = `
https://api.mapbox.com/
styles/v1/sisbos/cl07xga7c002616qcbxymnn5z/
static/
path-` + routePageMap.routeWidth + `+` + routePageMap.routeColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
url-` + encodeURIComponent('https://img.icons8.com/flat-round/64/stop.png') + `(` + routeCoordinates[routeCoordinates.length - 1][0] + `,` + routeCoordinates[routeCoordinates.length - 1][1] + `),
url-` + encodeURIComponent('https://img.icons8.com/flat-round/64/play.png') + `(` + routeCoordinates[0][0] + `,` + routeCoordinates[0][1] + `)
` + checkpoints + `/
` + boundingBoxUri + `/
` + width + `x450@2x
?padding=10
&addlayer=` + seasonLayer + `
&before_layer=landuse
&access_token=pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdndjY1bTI4OTUzZG5wOGs5ZWVsNnUifQ.2BcHCFVvk0SWQOb5PejCdQ
`

console.log(url.length)

        // Display static map inside container
        $map.querySelector('img').src = url

        async function getRide () {
            return new Promise((resolve, reject) => {
                ajaxGetRequest (routePageMap.apiUrl + "?ride-load=" + routePageMap.rideId, async (ride) => resolve(ride))
            } )
        }

    }
    
} )