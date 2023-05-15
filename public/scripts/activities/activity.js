import Modal from "/class/Modal.js"
import CFUtils from "/class/utils/CFUtils.js"
import ActivityMap from "/class/maps/activity/ActivityMap.js"
import Polyline from '/node_modules/@mapbox/polyline/index.js'

// Specs listeners
document.querySelectorAll('.pg-ac-spec-container.front').forEach( (element) => {
    element.addEventListener('mouseenter', (e) => {
        e.target.style.display = 'none';
        e.target.nextElementSibling.style.display = 'flex';
    } )
} )
document.querySelectorAll('.pg-ac-spec-container.back').forEach( (element) => {
    element.addEventListener('mouseleave', (e) => {
        e.target.style.display = 'none';
        e.target.previousElementSibling.style.display = 'flex';
    } )
} )

var $map = document.querySelector('#activityMap')

// Instantiate activity map
var activityMap = new ActivityMap()

// Get activity data from server
ajaxGetRequest (activityMap.apiUrl + "?load=" + activityMap.activityId, async (activityData) => {

    // Clean route data architecture to match geojson format
    for (let i = 0; i < activityData.route.time.length; i++) {
        activityData.route.time[i] = new Date(activityData.route.time[i].date).getTime()
    }
    activityData.routeData = {
        geometry: {
            coordinates: activityData.route.coordinates,
            type: 'LineString'
        },
        properties: {
            time: activityData.route.time,
        },
        type: 'Feature'
    }
    activityData.checkpoints.forEach( (checkpoint) => {
        checkpoint.datetime = new Date(checkpoint.datetime.date).getTime()
    } )
    activityData.photos.forEach( (photo) => {
        photo.datetime = new Date(photo.datetime.date).getTime()
        photo.distance = activityMap.getPhotoDistance(photo, activityData.routeData)
        document.querySelectorAll('.pg-ac-photo').forEach($photo => { // Add distance to timeline photo elements
            if (parseInt($photo.dataset.id) == photo.id) $photo.parentElement.querySelector('.pg-ac-photo-distance').innerText = 'km ' + (Math.round(photo.distance * 10) / 10)
        })
    } )

    // Load activity data into map instance
    activityMap.data = activityData

    // Set month property to activity month
    activityMap.month = new Date(activityData.route.time[0]).getMonth() + 1
    activityMap.setSeason()
    
    // If map is interactive
    if ($map.getAttribute('interactive') == 'true') {
    
        // Set default layer according to current season
        var map = await activityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z', activityMap.data.routeData.geometry.coordinates[0])

        // Build controls
        activityMap.addStyleControl()
        activityMap.addOptionsControl()
        activityMap.addRouteControl()

        // Add route layer and paint route properties
        activityMap.setGrabber()
        activityMap.addSources()
        activityMap.addLayers()
        activityMap.addRouteLayer(activityMap.data.routeData)
        activityMap.profile.generate()
        ///activityMap.displayStartGoalMarkers(activityMap.data.routeData)
        activityMap.updateDistanceMarkers()
        activityMap.focus(activityMap.data.routeData).then(() => {
            activityMap.profile.generate({
                poiData: {
                    activityCheckpoints: activityMap.data.checkpoints
                }
            })
        })
        activityMap.displayCheckpointMarkers()
        await activityMap.displayPhotoMarkers()

        // Focus on checkpoint on icon or checkpoint topline click
        document.querySelectorAll('.pg-ac-checkpoint-topline').forEach( (icon) => {
            icon.addEventListener('click', (e) => {
                var checkpoint = activityMap.data.checkpoints[e.target.closest('.pg-ac-checkpoint-container').dataset.number]
                window.scrollTo(0, $map.offsetTop)
                map.flyTo( {
                    center: checkpoint.lngLat,
                    zoom: 12,
                    pitch: 0,
                    bearing: 0
                } )
                checkpoint.marker.togglePopup()
            } )
        } )

        // On click on a photo in the checkpoints container, focus on corresponding map point and grow the map photo
        document.querySelectorAll('.pg-ac-photo').forEach( (img) => {
            img.addEventListener('click', (e) => {
                var photoId = e.target.dataset.id
                var photo
                activityMap.data.photos.forEach( (photoData) => {
                    if (photoData.id == photoId) photo = photoData
                } )
                window.scrollTo(0, $map.offsetTop)
                map.easeTo( {
                    offset: [0, $map.offsetHeight / 2 - 40],
                    center: activityMap.getPhotoLocation(photo),
                    zoom: 12
                } )
                photo.marker.grow()
            } )
        } )

        // On click on a photo on the map, grow the photo
        document.querySelectorAll('.pg-ac-map-img').forEach( (img) => {
            img.addEventListener('click', (e) => {
                var photoId = e.target.parentElement.dataset.id
                var photo
                activityMap.data.photos.forEach( (photoData) => {
                    if (photoData.id == photoId) photo = photoData
                } )
                map.easeTo( {
                    offset: [0, $map.offsetHeight / 2 - 40],
                    center: activityMap.getPhotoLocation(photo),
                    zoom: 12
                } )
                photo.marker.grow()
            } )
        } )

        // On click on the map, elsewhere than on a photo, reset default marker size
        map.on('click', (e) => {
            var isImageOnPath
            e.originalEvent.composedPath().forEach(entry => {
                if (entry.className == 'pg-ac-map-img') isImageOnPath = true
            } )
            if (!isImageOnPath) {
                activityMap.unselectPhotos()
            }
        } )

    } else {

        // Hide profile and grabber element
        document.querySelector('#profileBox').style.display = 'none'
        document.querySelector('.grabber').style.display = 'none'

        // Build seasons layer
        var colors = activityMap.getSeasonalColors(activityMap.season)
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
        const routeData = activityMap.data.routeData
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
        const checkpointMarkerColor = 'ffffff'

        // Build checkpoints
        var checkpoints = ''
        const iconsFolder = 'https://www.cyclingfriends.co/media/activity-icons/'
        if (activityMap.data.checkpoints) {
            activityMap.data.checkpoints.forEach(checkpoint => {
                // remove start and goal markers
                if (!checkpoint.special) checkpoints += ',url-' + encodeURIComponent(iconsFolder + 'w_' + checkpoint.type + '.png') + '('  + checkpoint.lngLat.lng + ',' + checkpoint.lngLat.lat + ')'
            } )
        }

        // Build photos
        const photosMarkerColor = activityMap.routeColor.slice(1)
        var photos = ''
        if (activityMap.data.photos.length > 0) {
            var number = 1
            activityMap.data.photos.forEach(photo => {
                let coord = activityMap.getPhotoLocation(photo)
                photos += ',pin-l-' + number + '+' + photosMarkerColor + '('  + coord[0] + ',' + coord[1] + ')'
                number++
            } )
        }

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
path-` + (activityMap.routeWidth + 4) + `+` + activityMap.routeCapColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
path-` + activityMap.routeWidth + `+` + activityMap.routeColor.slice(1) +  `-1(` + staticRoutePolylineUri + `),
url-` + encodeURIComponent('https://img.icons8.com/flat-round/64/stop.png') + `(` + routeCoordinates[routeCoordinates.length - 1][0] + `,` + routeCoordinates[routeCoordinates.length - 1][1] + `),
url-` + encodeURIComponent('https://img.icons8.com/flat-round/64/play.png') + `(` + routeCoordinates[0][0] + `,` + routeCoordinates[0][1] + `)
`
 + checkpoints
 + photos + `/
` + boundingBoxUri + `/
` + width + `x450@2x
?padding=10
&addlayer=` + seasonLayer + `
&before_layer=landuse
&access_token=pk.eyJ1Ijoic2lzYm9zIiwiYSI6ImNsMDdndjY1bTI4OTUzZG5wOGs5ZWVsNnUifQ.2BcHCFVvk0SWQOb5PejCdQ
`

        // Display static map inside container
        $map.querySelector('img').src = url
        
        // On click on a photo in the checkpoints container, open modal window
        document.querySelectorAll('.pg-ac-photo').forEach( ($img) => {
            var modal = new Modal($img.src)
            $img.after(modal.element)
            activityMap.data.photos.forEach(photo => { // Add distance to timeline photo elements
                if (parseInt($img.dataset.id) == photo.id) {
                    let moment = new Date(photo.datetime - activityMap.data.route.time[0])
                    moment.setHours(moment.getHours() - 9)
                    let distance = 'km ' + (Math.round(photo.distance * 10) / 10)
                    modal.setCaption(distance, moment.getHours() + 'h' + moment.getMinutes())
                }
            })
            $img.addEventListener('click', () => {
                modal.open()
            } )
        } )

    }

} )
