import CFUtils from "/class/utils/CFUtils.js"
import Modal from "/class/Modal.js"
import Profile from "/class/Profile.js"
import SegmentMap from "/class/maps/segment/SegmentMap.js"
import Polyline from '/node_modules/@mapbox/polyline/index.js'

var segmentMap = new SegmentMap()

var $map = document.getElementById('segmentMap')
const exportButton = document.querySelector('#export')
const deleteButton = document.querySelector('#delete')

// Set timeline container height
var timelineContainer = document.querySelector('.pg-sg-season-descriptions')
if (timelineContainer && timelineContainer.querySelector('p')) timelineContainer.style.height = (timelineContainer.querySelector('p').offsetHeight) + 'px'

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
    segmentMap.mapdata.segment = segment
    segmentMap.data = segment.route
    segmentMap.routeData = geojson

    // Load gpx file
    exportButton.href = CFUtils.loadGpx(geojson)
    exportButton.download = segment.route.name + '.gpx'
    
    // Set map instance paint property data
    if (segment.rank == 'local') segmentMap.routeColor = segmentMap.segmentLocalColor
    else if (segment.rank == 'regional') segmentMap.routeColor = segmentMap.segmentRegionalColor
    else if (segment.rank == 'national') segmentMap.routeColor = segmentMap.segmentNationalColor

    // On click on delete button, delete segment
    if (deleteButton) deleteButton.addEventListener('click', async () => {
        var answer = await openConfirmationPopup('このセグメント及びそれに関連するデータは全て削除されます。宜しいですか？')
        if (answer) ajaxGetRequest(segmentMap.apiUrl + "?segment-delete=" + segmentMap.segmentId, async (response) => {
            window.location.replace('/world')
        } )
    } )

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
        map.once('idle', () => segmentMap.profile.generate())
        
        // Focus
        segmentMap.map.jumpTo( {
            center: coordinates[0],
            pitch: 35,
        } )
        var routeBounds = CFUtils.defineRouteBounds(coordinates)
        map.fitBounds(routeBounds)
        
        // Paint route properties
        segmentMap.paintTunnels(segment.route.tunnels)
        segmentMap.updateDistanceMarkers()
        segmentMap.displayStartGoalMarkers(geojson)

        // Request and display sceneries close to the route
        segmentMap.loadCloseSceneries(5).then( async (sceneries) => {

            // Load sceneries into map instance
            segmentMap.mapdata.sceneries = sceneries

            // Regenerate profile to display them
            segmentMap.profile.generate({
                poiData: {
                    sceneries: segmentMap.mapdata.sceneries
                }
            })

            // Build route table and load route table buttons
            segmentMap.buildTable(['sceneries', 'checkpoints'])
            segmentMap.enableTableButtons()
        } )

    // If map is static
    } else {

        // Request and display sceneries close to the route
        segmentMap.loadCloseSceneries(2, {displayOnMap: false, generateProfile: false, getFileBlob: false}).then( async (sceneries) => {
            segmentMap.mapdata.sceneries = sceneries
            segmentMap.buildTable(['sceneries'])
            segmentMap.enableTableButtons()

            // Build profile
            segmentMap.profile = new Profile()
            segmentMap.profile.routeData = geojson
            segmentMap.profile.generate({precise: true})
        } )

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
        const routeData = segmentMap.routeData
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

        // Build sceneries
        segmentMap.mapdata.sceneries = await segmentMap.loadCloseSceneries(2, {displayOnMap: false, generateProfile: false, getFileBlob: false})
        const markerColor = 'fff'
        var sceneries = ''
        segmentMap.mapdata.sceneries.forEach(scenery => {
            if (scenery.on_route) sceneries += ',pin-s-' + Math.round(scenery.distance) + '+' + markerColor + '('  + scenery.lng + ',' + scenery.lat + ')'
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
` + sceneries + `/
` + boundingBoxUri + `/
` + width + `x450@2x
?padding=99
&addlayer=` + seasonLayer + `
&before_layer=landuse
&access_token=pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdndjY1bTI4OTUzZG5wOGs5ZWVsNnUifQ.2BcHCFVvk0SWQOb5PejCdQ
`

        // Display static map inside container
        $map.querySelector('img').src = url

    }

} )