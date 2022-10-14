import ActivityMap from "/map/class/ActivityMap.js"

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
ajaxGetRequest (activityMap.apiUrl + "?activity-load=" + activityMap.activityId, async (activityData) => {

    // Clean route data architecture to match geojson format
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

    // Load activity data into map instance
    activityMap.data = activityData
    console.log(activityMap.data)
    
    // Set default layer according to current season
    var map = await activityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z', activityMap.data.routeData.geometry.coordinates[0])

    // Build controls
    activityMap.addRouteControl()
    activityMap.addOptionsControl()
    activityMap.addStyleControl()

    // Add route layer and paint route properties
    activityMap.setGrabber()
    activityMap.addSources()
    activityMap.addLayers()
    activityMap.addRouteLayer(activityMap.data.routeData)
    activityMap.generateProfile()
    //activityMap.displayStartGoalMarkers(activityMap.data.routeData)
    activityMap.updateDistanceMarkers()
    activityMap.focus(activityMap.data.routeData)
    activityMap.displayCheckpointMarkers()
    activityMap.displayPhotos()

    // Focus on checkpoint on icon or checkpoint topline click
    document.querySelectorAll('.pg-ac-checkpoint-topline').forEach( (icon) => {
        icon.addEventListener('click', (e) => {
            var checkpoint = activityMap.data.checkpoints[e.target.closest('.pg-ac-checkpoint-container').dataset.number]
            window.scrollTo(0, $map.offsetTop)
            map.flyTo( {
                center: checkpoint.lngLat,
                zoom: 14,
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
                zoom: 14
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
                zoom: 14
            } )
            photo.marker.grow()
        } )
    } )

    // On click on the map, elsewhere than on a photo, reset default marker size
    map.on('click', (e) => {
        var isImageOnPath
        e.originalEvent.path.forEach(entry => {
            if (entry.className == 'pg-ac-map-img') isImageOnPath = true
        } )
        if (!isImageOnPath) {
            activityMap.unselectPhotos()
        }
    } )

} )
