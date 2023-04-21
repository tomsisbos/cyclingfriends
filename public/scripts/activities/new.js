import Loader from "/map/class/Loader.js"
import NewActivityMap from "/map/class/activity/NewActivityMap.js"

var $upload = document.querySelector('#uploadActivity')
var $map = document.querySelector('#activityMap')
var $form = document.querySelector('#activityForm')

// On activity upload
$upload.addEventListener('change', async (e) => {

    // Initialize
    var file = e.target.files[0]
    var reader = new FileReader()
    var xhr = new XMLHttpRequest()
    xhr.open("POST", "/api/activities/upload.php")
    var formData = new FormData()
    formData.append('activity', file)
    var loader = new Loader()
    loader.prepare('データを準備中...')
    loader.start()
    window.setTimeout(() => {
        if (loader.isSet()) loader.appendText('ファイルデーターが大きいと、解析に時間がかかる場合があります。お手数をおかけしますが、もうしばらくお待ちください。')
    }, 10000)

    // Start file upload
    if (file instanceof Blob) reader.readAsArrayBuffer(file)

    // When upload has finished, send it to server
    reader.onload = () => {
        loader.setText('データをアップロード中... (1/2)')
        xhr.send(formData)
    }

    // When request has been sent
    xhr.onreadystatechange = async () => {

        // When response is received
        if (xhr.readyState === 4) {
            
            var response = JSON.parse(xhr.responseText)

            // If successful response, start parsing
            if (xhr.status == '200') {

                loader.setText('データを解析中... (2/2)')

                // If file upload has succeed
                if (response.success) {

                    // Instantiate activity map
                    var newActivityMap = new NewActivityMap()

                    // Clear data and elements if necessary
                    newActivityMap.clearForm()

                    // Format data from parsed js object
                    if (response.filetype == 'fit') var response = await newActivityMap.importDataFromFit(response.file)
                    else if (response.filetype == 'gpx') var response = await newActivityMap.importDataFromGpx(response.file)

                    loader.stop()

                    // Get activity data
                    if (response.success) {

                        // Display and prefill form
                        document.querySelector('#topContainer').style.borderBottom = '1px solid #ced4da'
                        hideResponseMessage()
                        $form.style.display = 'block'
                        newActivityMap.updateForm()
                        document.querySelector('#selectBikes').addEventListener('change', e => newActivityMap.data.bike_id = e.target.value)
                        document.querySelector('#selectPrivacy').addEventListener('change', e => newActivityMap.data.privacy = e.target.value)

                        // Load map on first upload
                        if (!newActivityMap.loaded) {
                            newActivityMap.map = await newActivityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
                            newActivityMap.addRouteControl( {
                                displaySceneries: false,
                                flyAlong: false
                            } )
                            newActivityMap.map.once('load', () => newActivityMap.map.resize())
                        }

                        // Add route layer and paint route properties
                        newActivityMap.setGrabber()
                        newActivityMap.addSources()
                        newActivityMap.addLayers()
                        newActivityMap.addRouteLayer(newActivityMap.data.routeData)
                        newActivityMap.displayStartGoalMarkers(newActivityMap.data.routeData)
                        newActivityMap.updateDistanceMarkers()
                        newActivityMap.focus(newActivityMap.data.routeData)

                        // Add photos treatment
                        document.querySelector('#uploadPhotos').addEventListener('change', async (e) => {
                            newActivityMap.loadPhotos(e.target.files).then(() => newActivityMap.updatePhotos())
                        } )
                        document.querySelector('#clearPhotos').addEventListener('click', () => newActivityMap.clearPhotos())
                        document.querySelector('#changePhotosPrivacy').addEventListener('click', () => newActivityMap.changePhotosPrivacy())

                        // Save activity treatment
                        document.querySelector('#saveActivity').addEventListener('click', async () => {
                            var photosToShare = await newActivityMap.checkForCloseSceneries()
                            if (newActivityMap.data.sceneriesToCreate && newActivityMap.data.sceneriesToCreate.length > 0) var sceneriesToCreate = await newActivityMap.createSceneries()
                            else var sceneriesToCreate = null
                            newActivityMap.saveActivity(photosToShare, sceneriesToCreate)
                        } )

                        // Create new checkpoint on click on route
                        newActivityMap.map.on('mouseenter', 'route', () => newActivityMap.map.getCanvas().style.cursor = 'crosshair')
                        newActivityMap.map.on('mouseleave', 'route', () => newActivityMap.map.getCanvas().style.cursor = 'grab')
                        newActivityMap.map.on('click', 'route', async (e) => {
                            await newActivityMap.addMarkerOnRoute(e.lngLat)
                            newActivityMap.updatePhotos()
                        } )

                        // Change photos privacy to private if activity privacy is set to private
                        document.querySelector('#selectPrivacy').addEventListener('change', (e) => {
                            if (e.target.value == 'private') {
                                newActivityMap.data.photos.forEach(photo => {
                                    photo.privacy = 'private'
                                    newActivityMap.updatePrivacyButton(photo)
                                })
                            }
                        })

                    // Else, display error message
                    } else if (response.error) {
                        loader.stop()
                        showResponseMessage(response)
                    }

                } else if (response.error) {
                    loader.stop()
                    showResponseMessage(response)
                }

            } else {
                loader.stop()
                showResponseMessage(response)
            }
        }
    }
} )