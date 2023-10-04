import EditActivityMap from "/class/maps/activity/EditActivityMap.js"

var $map = document.querySelector('#activityMap')
var $form = document.querySelector('#activityForm')

// Instantiate activity map
var editActivityMap = new EditActivityMap()

// Clear data and elements if necessary
editActivityMap.clearForm()

// Get activity data from server
ajaxGetRequest ("/api/activity.php" + "?load=" + editActivityMap.activityId, async (activityData) => {
    
    // Load activity data into map instance
    editActivityMap.data = activityData
    
    // Add photos treatment
    document.querySelector('#uploadPhotos').addEventListener('change', async (e) => {
        editActivityMap.loadPhotos(e.target.files)
        .then(async () => editActivityMap.updatePhotos())
        .then(() => editActivityMap.displayPhotoMarkers())
    } )
    document.querySelector('#clearPhotos').addEventListener('click', () => editActivityMap.clearPhotos())
    document.querySelector('#changePhotosPrivacy').addEventListener('click', () => editActivityMap.changePhotosPrivacy())

    // Clean data architecture to match instance data format
    editActivityMap.routeData = {
        geometry: {
            coordinates: activityData.route.coordinates,
            type: 'LineString'
        },
        properties: {
            time: activityData.route.time,
        },
        type: 'Feature'
    }
    editActivityMap.mapdata.sceneries = await editActivityMap.loadCloseSceneries(1, {displayOnMap: false, generateProfile: false, getFileBlob: false}),

    // Display and prefill form
    hideResponseMessage()
    $form.style.display = 'block'
    editActivityMap.populateForm()
    editActivityMap.updatePhotos()
    document.querySelector('#inputTitle').addEventListener('change', e => editActivityMap.data.title = e.target.value)
    document.querySelector('#selectBikes').addEventListener('change', e => editActivityMap.data.bike_id = e.target.value)
    document.querySelector('#selectPrivacy').addEventListener('change', e => editActivityMap.data.privacy = e.target.value)

    // Load map on first upload
    if (!editActivityMap.loaded) {
        editActivityMap.map = await editActivityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
        editActivityMap.map.once('load', () => editActivityMap.map.resize())
    }

    // Add route layer and paint route properties
    editActivityMap.setGrabber()
    editActivityMap.addSources()
    editActivityMap.addLayers()
    editActivityMap.addRouteLayer(editActivityMap.routeData)
    editActivityMap.displayStartGoalMarkers(editActivityMap.routeData)
    editActivityMap.updateDistanceMarkers()
    editActivityMap.focus(editActivityMap.routeData)
    editActivityMap.profile.generate()
    editActivityMap.displayCheckpointMarkers()
    editActivityMap.displayPhotoMarkers()

    // Create new checkpoint on click on route
    editActivityMap.map.on('mouseenter', 'route', () => editActivityMap.map.getCanvas().style.cursor = 'crosshair')
    editActivityMap.map.on('mouseleave', 'route', () => editActivityMap.map.getCanvas().style.cursor = 'grab')
    editActivityMap.map.on('click', 'route', async (e) => {
        await editActivityMap.addMarkerOnRoute(e.lngLat)
        editActivityMap.updatePhotos()
    } )

    // Change photos privacy to private if activity privacy is set to private
    document.querySelector('#selectPrivacy').addEventListener('change', (e) => {
        if (e.target.value == 'private') {
            editActivityMap.data.photos.forEach(photo => {
                photo.privacy = 'private'
                editActivityMap.updatePrivacyButton(photo)
            })
        }
    })
    
    // Save activity treatment
    document.querySelector('#saveActivity').addEventListener('click', async () => {
        const photosToShare = null // In case of ride editing, don't check for sceneries to which add photos
        if (editActivityMap.data.sceneriesToCreate) var sceneriesToCreate = await editActivityMap.createSceneries()
        else var sceneriesToCreate = null
        editActivityMap.saveActivity(photosToShare, sceneriesToCreate)
    } )

} )