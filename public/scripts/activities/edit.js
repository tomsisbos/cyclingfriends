import EditActivityMap from "/map/class/EditActivityMap.js"

var $map = document.querySelector('#activityMap')
var $form = document.querySelector('#activityForm')

// Instantiate activity map
var editActivityMap = new EditActivityMap()

// Clear data and elements if necessary
editActivityMap.clearForm()

// Get activity data from server
ajaxGetRequest ("/actions/activities/activityApi.php" + "?activity-load=" + editActivityMap.activityId, async (activityData) => {
    
    // Load activity data into map instance
    editActivityMap.data = activityData

    // Clean data architecture to match instance data format
    for (let i = 0; i < activityData.route.time.length; i++) {
        activityData.route.time[i] = new Date(activityData.route.time[i].date).getTime()
    }
    editActivityMap.data.routeData = {
        geometry: {
            coordinates: activityData.route.coordinates,
            type: 'LineString'
        },
        properties: {
            time: activityData.route.time,
        },
        type: 'Feature'
    }
    editActivityMap.data.checkpoints.forEach( (checkpoint) => {
        checkpoint.datetime = new Date(checkpoint.datetime.date).getTime()
    } )
    editActivityMap.data.photos.forEach( (photo) => {
        photo.datetime = new Date(photo.datetime.date).getTime()
    } )

    // Display and prefill form
    hideResponseMessage()
    $form.style.display = 'block'
    editActivityMap.updateForm()
    editActivityMap.updatePhotos()
    document.querySelector('#selectBikes').addEventListener('change', e => editActivityMap.data.bike_id = e.target.value)
    document.querySelector('#selectPrivacy').addEventListener('change', e => editActivityMap.data.privacy = e.target.value)

    // Load map on first upload
    if (!editActivityMap.loaded) {
        editActivityMap.map = await editActivityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
        editActivityMap.map.once('load', () => editActivityMap.map.resize())
    }

    // Add route layer and paint route properties
    console.log(editActivityMap)
    editActivityMap.setGrabber()
    editActivityMap.addSources()
    editActivityMap.addLayers()
    editActivityMap.addRouteLayer(editActivityMap.data.routeData)
    ///editActivityMap.displayStartGoalMarkers(editActivityMap.data.routeData)
    editActivityMap.updateDistanceMarkers()
    editActivityMap.focus(editActivityMap.data.routeData)
    editActivityMap.displayCheckpointMarkers()
    editActivityMap.displayPhotos()

    // Add photos treatment
    document.querySelector('#uploadPhotos').addEventListener('change', async (e) => {
        editActivityMap.loadPhotos(e.target.files).then( () => {
            editActivityMap.updatePhotos()
        } )
    } )

    // Create new checkpoint on click on route
    editActivityMap.map.on('mouseenter', 'route', () => editActivityMap.map.getCanvas().style.cursor = 'crosshair')
    editActivityMap.map.on('mouseleave', 'route', () => editActivityMap.map.getCanvas().style.cursor = 'grab')
    editActivityMap.map.on('click', 'route', (e) => {
        editActivityMap.addMarkerOnRoute(e.lngLat)
        console.log(editActivityMap.data.checkpoints)
        editActivityMap.updatePhotos()
    } )
    
    // Save activity treatment
    document.querySelector('#saveActivity').addEventListener('click', async () => {
        editActivityMap.saveActivity()
    } )

} )