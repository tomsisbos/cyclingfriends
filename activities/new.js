import NewActivityMap from "/map/class/NewActivityMap.js"

var $upload = document.querySelector('#uploadActivity')
var $map = document.querySelector('#activityMap')
var $form = document.querySelector('#activityForm')

// On activity upload
$upload.addEventListener('change', async (e) => {

    // File treatment
    new UploadFile(e.target, e.target.files[0], displayData)

    function UploadFile (element, file, callback) {

        var reader = new FileReader()
        this.ctrl = createThrobber(element)
        var xhr = new XMLHttpRequest()
        const formData = new FormData()
        formData.append('activity', file)
        this.xhr = xhr
        
        var self = this
        this.xhr.upload.addEventListener("progress", (e) => {
                if (e.lengthComputable) {
                var percentage = Math.round((e.loaded * 100) / e.total)
                self.ctrl.update(percentage)
                }
            }, false)
        
        xhr.upload.addEventListener("load", () => {
                self.ctrl.update(100)
                var canvas = self.ctrl.ctx.canvas
                canvas.parentNode.removeChild(canvas)
            }, false)
        xhr.open("POST", "/actions/activities/uploadAction.php")
        // xhr.overrideMimeType('text/plain; charset=x-user-defined-binary')

        reader.onload = () => xhr.send(formData)

        xhr.onreadystatechange = () => {
            // On success, execute callback
            if (xhr.readyState === 4) callback(JSON.parse(xhr.responseText))
        }

        reader.readAsBinaryString(file)

    }

    // Create loading throbber element
    function createThrobber (element) {
        const throbberWidth = '100%'
        const throbberHeight = 6
        const throbber = document.createElement('canvas')
        throbber.classList.add('upload-progress')
        throbber.setAttribute('width', throbberWidth)
        throbber.setAttribute('height', throbberHeight)
        element.parentNode.appendChild(throbber)
        throbber.ctx = throbber.getContext('2d')
        throbber.ctx.fillStyle = 'orange'
        throbber.update = (percent) => {
            throbber.ctx.fillRect(0, 0, throbberWidth * percent / 100, throbberHeight)
            if (percent === 100) {
                throbber.ctx.fillStyle = 'green'
            }
        }
        throbber.update(0)
        return throbber
    }

    // After file upload
    async function displayData (response) {

        // If file upload has succeed, display activity form
        if (response.success) {

            // Separate top container with form
            document.querySelector('#topContainer').style.borderBottom = '1px solid #ced4da'
            document.querySelector('#topContainer').style.marginBottom = '40px'

            // Instantiate activity map
            var newActivityMap = new NewActivityMap()

            // Clear data and elements if necessary
            newActivityMap.clearForm()

            // Format data from parsed js object
            if (response.filetype == 'fit') var response = await newActivityMap.importDataFromFit(response.file)
            else if (response.filetype == 'gpx') var response = await newActivityMap.importDataFromGpx(response.file)

            // Get activity data
            if (response.success) {
                console.log(newActivityMap.data)
                // Display and prefill form
                hideResponseMessage()
                $form.style.display = 'block'
                newActivityMap.updateForm()
                document.querySelector('#selectBikes').addEventListener('change', e => newActivityMap.data.bike_id = e.target.value)
                document.querySelector('#selectPrivacy').addEventListener('change', e => newActivityMap.data.privacy = e.target.value)

                // Load map on first upload
                if (!newActivityMap.loaded) {
                    newActivityMap.map = await newActivityMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
                    newActivityMap.map.once('load', () => newActivityMap.map.resize())
                }

                // Add route layer and paint route properties
                console.log(newActivityMap.data)
                newActivityMap.setGrabber()
                newActivityMap.addSources()
                newActivityMap.addLayers()
                newActivityMap.addRouteLayer(newActivityMap.data.routeData)
                newActivityMap.displayStartGoalMarkers(newActivityMap.data.routeData)
                newActivityMap.updateDistanceMarkers()
                newActivityMap.focus(newActivityMap.data.routeData)

                // Add photos treatment
                document.querySelector('#uploadPhotos').addEventListener('change', async (e) => {
                    newActivityMap.loadPhotos(e.target.files).then( () => {
                        newActivityMap.updatePhotos()
                    } )
                } )

                // Save activity treatment
                document.querySelector('#saveActivity').addEventListener('click', async () => {
                    newActivityMap.saveActivity()
                } )

            } else showResponseMessage(response)

            // Create new checkpoint on click on route
            newActivityMap.map.on('mouseenter', 'route', () => newActivityMap.map.getCanvas().style.cursor = 'crosshair')
            newActivityMap.map.on('mouseleave', 'route', () => newActivityMap.map.getCanvas().style.cursor = 'grab')
            newActivityMap.map.on('click', 'route', (e) => {
                newActivityMap.addMarkerOnRoute(e.lngLat)
                newActivityMap.updatePhotos()
            } )
        }

        // Else, display error message
        else if (response.error) showResponseMessage(response)
    }
} )
