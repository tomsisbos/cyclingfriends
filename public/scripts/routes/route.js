import CFUtils from "/class/utils/CFUtils.js"
import RouteMap from "/class/maps/route/RouteMap.js"
import Polyline from '/node_modules/@mapbox/polyline/index.js'
import Profile from '/class/Profile.js'

var routeMap = new RouteMap()

var $map = document.getElementById('routeMap')

const exportButton = document.querySelector('#export')

// Get route data from server
if (routeMap.routeId) var queryString = "?route-load=" + routeMap.routeId
else var queryString = "?route-load-from-ride=" + routeMap.rideId
ajaxGetRequest (routeMap.apiUrl + queryString, async (route) => {
    
    routeMap.routeId = route.id
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
    routeMap.data = route
    routeMap.routeData = geojson
    
    // Set map instance paint property data
    if (routeMap.rideId) routeMap.routeColor = '#FFFF00'

    // If map is interactive
    if ($map.getAttribute('interactive') == 'true') {
    
        // Set default layer according to current season
        var map = await routeMap.load($map, routeMap.defaultStyle, coordinates[0])
        
        // Set grabber 
        routeMap.setGrabber()

        // Display CF Layers
        routeMap.addSources()
        routeMap.addLayers()

        // Controls
        routeMap.addStyleControl()
        routeMap.addRouteControl()

        document.querySelector('.mapboxgl-ctrl-logo').style.display = 'none'
        map.addControl(
            new mapboxgl.GeolocateControl( {
                positionOptions: {
                    enableHighAccuracy: true
                }
            } )
        )
        
        // Display route
        routeMap.addRouteLayer(geojson)
        
        // Generate profile on idle
        routeMap.profile.generate()
        
        // Focus
        var routeBounds = CFUtils.defineRouteBounds(coordinates)
        map.fitBounds(routeBounds)
        
        // Paint route properties
        routeMap.paintTunnels(route.tunnels)
        routeMap.updateDistanceMarkers()
        if (!routeMap.rideId) routeMap.displayStartGoalMarkers(geojson)

        // Request and display sceneries close to the route
        routeMap.loadCloseSceneries(2).then( async (sceneries) => {

            // Load sceneries into map instance
            routeMap.mapdata.sceneries = sceneries
            
            // Generate profile with sceneries data
            routeMap.profile.generate({
                poiData: {sceneries}
            })
            
            // If ride ID is found inside query string parameters, get ride data from server
            if (routeMap.rideId) await routeMap.loadRide()
                
            // Build route specs table
            if (document.querySelector('#routeSlider')) routeMap.buildSlider()
            routeMap.buildTable(['sceneries', 'checkpoints'])
            routeMap.enableTableButtons()
        } )

        /*
        var fittingSegments = await routeMap.getFittingSegments()
        fittingSegments.forEach( (segment) => {
            routeMap.displaySegment(segment)
        } )*/

    // If map is static
    } else {

        // Hide grabber element and build profile
        document.querySelector('.grabber').style.display = 'none'
        routeMap.profile = new Profile()
        routeMap.profile.routeData = geojson
        routeMap.profile.generate({precise: true})

        // Build seasons layer
        var colors = routeMap.getSeasonalColors(routeMap.season)
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
        const routeData = routeMap.routeData
        const routeCoordinates = routeData.geometry.coordinates
        if (routeData.geometry.coordinates.length < 10000) var tolerance = 0.0005
        else var tolerance = 0.002
        var staticRouteData = turf.simplify(routeData, {tolerance, highQuality: true})
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
        if (routeMap.rideId) {
            routeMap.ride = await getRide()
            
            const markerColor = routeMap.routeColor.slice(1)
            var number = 0
            routeMap.ride.checkpoints.forEach(checkpoint => {
                if (!checkpoint.special.length > 0) checkpoints += ',pin-l-' + number + '+' + markerColor + '('  + checkpoint.lngLat.lng + ',' + checkpoint.lngLat.lat + ')' // remove start and goal markers
                number++
            } )
        }

        // Request and display sceneries close to the route
        routeMap.loadCloseSceneries(2, {displayOnMap: false, generateProfile: false, getFileBlob: false}).then( async (sceneries) => {
            routeMap.mapdata.sceneries = sceneries
            routeMap.buildTable(['sceneries', 'checkpoints'])
            routeMap.enableTableButtons()
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
path-` + (routeMap.routeWidth + 4) + `+` + routeMap.routeCapColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
path-` + routeMap.routeWidth + `+` + routeMap.routeColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
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

        // Display static map inside container
        $map.querySelector('img').src = url

        async function getRide () {
            return new Promise((resolve, reject) => {
                ajaxGetRequest (routeMap.apiUrl + "?ride-load=" + routeMap.rideId, async (ride) => resolve(ride))
            } )
        }

    }
    
} )