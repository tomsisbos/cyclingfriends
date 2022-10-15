import ActivityMap from "/map/class/ActivityMap.js"
import GPX from '/node_modules/gpx-parser-builder/src/gpx.js';

export default class NewActivityMap extends ActivityMap {

    constructor () {
        super()
    }

    apiUrl = '/actions/activities/saveApi.php'
    data
    cursor = 2

    // Parse file data and store it inside map instance
    importDataFromGpx (gpxFile) {
        return new Promise( async (resolve, reject) => {
            const gpx = GPX.parse(gpxFile) // Parse GPX file

            // Build trackpoints and routeCoords
            const track = gpx.trk[0]
            const trkpt = track.trkseg[0].trkpt
            var trackpoints = []
            var routeCoords = []
            var routeTime = []
            for (let i = 0; i < trkpt.length; i++) {
                let trackpoint = {
                    lngLat: {
                        lng: trkpt[i].$.lon,
                        lat: trkpt[i].$.lat
                    },
                    elevation: trkpt[i].ele,
                    time: trkpt[i].time,
                }
                routeCoords.push([trkpt[i].$.lon, trkpt[i].$.lat])
                routeTime.push(trackpoint.time.getTime())
                if (trkpt[i].extensions) {
                    if (trkpt[i].extensions['gpxtpx:TrackPointExtension']['gpxtpx:atemp']) trackpoint.temperature = trkpt[i].extensions['gpxtpx:TrackPointExtension']['gpxtpx:atemp']
                    if (trkpt[i].extensions['gpxtpx:TrackPointExtension']['gpxtpx:cad']) trackpoint.cadence = trkpt[i].extensions['gpxtpx:TrackPointExtension']['gpxtpx:cad']
                    if (trkpt[i].extensions.power) trackpoint.power = trkpt[i].extensions.power
                }
                trackpoints.push(trackpoint)
            }

            // Build max speed, max altitude and max slope
            var speed_max = 0
            var altitude_max = 0
            var slope_max = 0
            var duration_running = 0
            const precision = 10 // Calcul interval in seconds
            for (let i = 0; i < routeCoords.length; i += precision) {
                // Build max altitude
                if (parseInt(trackpoints[i].elevation) > altitude_max) {
                    altitude_max = Math.round(trackpoints[i].elevation)
                }
                if (routeCoords[i - precision]) {
                    // Build max speed
                    let distance = 0
                    for (let j = 0; j < precision; j++) distance += turf.distance(routeCoords[i - j], routeCoords[i - j - 1])
                    let seconds = (trackpoints[i].time - trackpoints[i - precision].time) / 1000
                    let hours = seconds / 60 / 60
                    var speed = distance / hours
                    if (speed > speed_max) speed_max = Math.round(speed * 10) / 10
                    // Build max slope
                    let elevation = parseInt(trackpoints[i].elevation) - parseInt(trackpoints[i - precision].elevation)
                    if (distance * 1000 > 10) var slope = elevation * 100 / (distance * 1000) // Prevent inaccurate calculation caused by too short section distance
                    if (slope > slope_max) slope_max = Math.round(slope * 10) / 10
                    // Build time running
                    if (distance > 0.015) duration_running += (trackpoints[i].time.getTime() - trackpoints[i - precision].time.getTime())
                    if (seconds > precision) duration_running -= (seconds - precision) * 1000 // Substact auto pause
                }
            }
            console.log('slope max : ' + slope_max)
            console.log('altitude max : ' + altitude_max)
            console.log('duration running : ' + getFormattedDurationFromTimestamp(duration_running))

            // Dynamically simplify routeCoords and routeTime
            if (trackpoints.length < 6000) var simplificationMultiplicator = 3
            else simplificationMultiplicator = 4
            for (let i = 0; i < routeCoords.length; i++) {
                routeCoords.splice(i, simplificationMultiplicator)
                routeTime.splice(i, simplificationMultiplicator)
            }
            // Build route geojson
            var routeData = turf.lineString(routeCoords)
            routeData.properties.time = routeTime

            // Build temperature
            if (trackpoints[0].temperature) {
                var sumTemperature = 0
                var minTemperature = 100
                var maxTemperature = -100
                for (let i = 0; i < trackpoints.length; i++) {
                    sumTemperature += parseInt(trackpoints[i].temperature)
                    if (trackpoints[i].temperature < minTemperature) minTemperature = parseInt(trackpoints[i].temperature)
                    if (trackpoints[i].temperature > maxTemperature) maxTemperature = parseInt(trackpoints[i].temperature)
                }
                var avgTemperature = Math.floor(sumTemperature / trackpoints.length * 10) / 10
            }

            // Build duration
            const endDate = trackpoints[trackpoints.length - 1].time.getTime()
            const startDate = trackpoints[0].time.getTime()
            var duration = getDurationFromTimestamp(endDate - startDate)
            // If no time data, display an error message
            if (endDate - startDate <= 60) resolve({error: 'This file doesn\'t have time data. It can\'t be saved as an activity.'})
            else {
                // Build start and end checkpoints
                var checkpoints = []
                var startPoint = {
                    name: 'Start',
                    type: 'Start',
                    story: '',
                    number: 0,
                    lngLat: trackpoints[0].lngLat,
                    datetime: startDate,
                    geolocation: await this.getCourseGeolocation(trackpoints[0].lngLat),
                    elevation: Math.floor(trackpoints[0].elevation),
                    distance: 0,
                    temperature: parseInt(trackpoints[0].temperature)
                }
                var goalPoint = {
                    name: 'Goal',
                    type: 'Goal',
                    story: '',
                    number: 1,
                    lngLat: trackpoints[trackpoints.length - 1].lngLat,
                    datetime: endDate,
                    geolocation: await this.getCourseGeolocation(trackpoints[trackpoints.length - 1].lngLat),
                    elevation: Math.floor(trackpoints[trackpoints.length - 1].elevation),
                    distance: Math.floor(turf.length(routeData) * 10) / 10,
                    temperature: parseInt(trackpoints[trackpoints.length - 1].temperature)
                }
                checkpoints.push(startPoint)
                checkpoints.push(goalPoint)

                // Build data
                this.data = {
                    title: track.name,
                    distance: Math.ceil(turf.length(routeData) * 10) / 10,
                    duration,
                    duration_running: getDurationFromTimestamp(duration_running),
                    bike_id: document.querySelector('#selectBikes').value,
                    privacy: document.querySelector('#selectPrivacy').value,
                    elevation: Math.floor(this.calculateElevation(trackpoints)),
                    speed_max,
                    altitude_max,
                    slope_max,
                    temperature: {
                        min: minTemperature,
                        avg: avgTemperature,
                        max: maxTemperature
                    },
                    routeData,
                    checkpoints,
                    photos: [],
                    trackpoints
                }
                resolve({success: true})
            }
        } )
    }

    updateForm () {
        const $form = document.querySelector('#activityForm')
        var $title = $form.querySelector('#inputTitle')
        var $start = $form.querySelector('#divStart')
        var $goal = $form.querySelector('#divGoal')
        var $distance = $form.querySelector('#divDistance')
        var $duration = $form.querySelector('#divDuration')
        var $elevation = $form.querySelector('#divElevation')
        var $minTemperature = $form.querySelector('#divMinTemperature')
        var $avgTemperature = $form.querySelector('#divAvgTemperature')
        var $maxTemperature = $form.querySelector('#divMaxTemperature')
        if (this.data.title != $title.value) $title.value = this.data.title
        $start.innerHTML = '<strong>Start : </strong>' + this.data.checkpoints[0].geolocation.city + ' (' + this.data.checkpoints[0].geolocation.prefecture + ')'
        $goal.innerHTML = '<strong>Goal : </strong>' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.city + ' (' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.prefecture + ')'
        $distance.innerHTML = '<strong>Distance : </strong>' + this.data.distance + 'km'
        $duration.innerHTML = '<strong>Duration : </strong>' + getFormattedDurationFromTimestamp(this.data.trackpoints[this.data.trackpoints.length - 1].time.getTime() - this.data.trackpoints[0].time.getTime())
        $elevation.innerHTML = '<strong>Elevation : </strong>' + this.data.elevation + 'm'
        $minTemperature.innerHTML = '<strong>Min. Temperature : </strong>' + this.data.temperature.min + '°C'
        $avgTemperature.innerHTML = '<strong>Avg. Temperature : </strong>' + this.data.temperature.avg + '°C'
        $maxTemperature.innerHTML = '<strong>Max. Temperature : </strong>' + this.data.temperature.max + '°C'
        this.updateCheckpointForms()
    }

    clearForm () {
        document.querySelector('#divCheckpoints').innerHTML = ''
    }

    updateCheckpointForms () {

        var $checkpoints = document.querySelector('#divCheckpoints')

        // Sort by distance
        this.data.checkpoints.sort( (a, b) => {
            return a.distance - b.distance
        } )

        // Build elements
        this.data.checkpoints.forEach( (checkpoint) => {
            if (!checkpoint.form) { // Only build checkpoint form elements if not existing yet
                checkpoint.form = document.createElement('div')
                checkpoint.form.id = 'checkpointForm' + checkpoint.number
                checkpoint.form.className = 'new-ac-checkpoint'
                var $photosContainer = document.createElement('div')
                $photosContainer.className = 'pg-ac-photos-container'
                var $topline = document.createElement('div')
                $topline.className = 'new-ac-checkpoint-topline'
                var $name = document.createElement('input')
                $name.className = 'form-control'
                $name.placeholder = 'Name...'
                if (checkpoint.name) $name.value = checkpoint.name
                var $properties = document.createElement('div')
                $properties.className = 'new-ac-checkpoint-properties form-control-plaintext'
                var $distance = document.createElement('div')
                $distance.innerHTML = '<strong>km ' + (Math.ceil(checkpoint.distance * 10) / 10) + '</strong>'
                var $datetime = document.createElement('div')
                $datetime.innerHTML = '\u00a0- ' + getFormattedDurationFromTimestamp(checkpoint.datetime - this.data.checkpoints[0].datetime)
                if (checkpoint.type == 'Start' || checkpoint.type == 'Goal') {
                    var $type = document.createElement('div')
                    $type.innerHTML = checkpoint.type
                    $type.setAttribute('readonly', true)
                    $type.className = 'form-control-plaintext'
                } else {
                    var $type = buildCheckpointSelectType()
                    $type.className = 'form-select'
                }
                var $story = document.createElement('textarea')
                $story.className = 'form-control'
                $story.placeholder = 'Story...'
                $properties.appendChild($distance)
                $properties.appendChild($datetime)
                $topline.appendChild($name)
                $topline.appendChild($properties)
                $topline.appendChild($type)
                checkpoint.form.appendChild($photosContainer)
                checkpoint.form.appendChild($topline)
                checkpoint.form.appendChild($story)
                // Append element
                if (document.querySelector('#checkpointForm' + (checkpoint.number - 1))) {
                    document.querySelector('#checkpointForm' + (checkpoint.number - 1)).after(checkpoint.form)
                } else $checkpoints.appendChild(checkpoint.form)
                // Build arrow
                if (checkpoint.type != 'Goal') {
                    let xmlns = "http://www.w3.org/2000/svg"
                    let svg = document.createElementNS(xmlns, "svg")
                    svg.setAttribute('height', 10)
                    svg.setAttribute('width', 120)
                    svg.style.alignSelf = 'center'
                    let polygon = document.createElementNS(xmlns, "polygon")
                    polygon.setAttribute('points', '00,00 60,10 120,00')
                    svg.appendChild(polygon)
                    checkpoint.form.appendChild(svg)
                }

                // Add listeners
                $name.addEventListener('change', e => {
                    checkpoint.name = e.target.value
                } )
                $story.addEventListener('change', e => checkpoint.story = e.target.value)
                $type.addEventListener('change', e => checkpoint.type = e.target.value)
            }
        } )

        function buildCheckpointSelectType () {
            var $type = document.createElement('select')
            // Landscape
            let $landscape = document.createElement('option')
            $landscape.value = 'Landscape'
            $landscape.text = 'Landscape'
            $type.add($landscape)
            // Break
            let $break = document.createElement('option')
            $break.value = 'Break'
            $break.text = 'Break'
            $type.add($break)
            // Restaurant
            let $restaurant = document.createElement('option')
            $restaurant.value = 'Restaurant'
            $restaurant.text = 'Restaurant'
            $type.add($restaurant)
            // Cafe
            let $coffee = document.createElement('option')
            $coffee.value = 'Cafe'
            $coffee.text = 'Cafe'
            $type.add($coffee)
            // Attraction
            let $attraction = document.createElement('option')
            $attraction.value = 'Attraction'
            $attraction.text = 'Attraction'
            $type.add($attraction)
            // Event
            let $event = document.createElement('option')
            $event.value = 'Event'
            $event.text = 'Event'
            $type.add($event)
            return $type
        }
    }
    
    removeOnClickHandler = (e) => {
        var removeOnClick = this.removeOnClick.bind(this, e)
        removeOnClick()
    }

    // Treat user left click on marker
    removeOnClick (e) {
        var number = parseInt(e.target.innerHTML)
        this.data.checkpoints[number].marker.remove()
        this.data.checkpoints[number].form.remove()
        this.data.checkpoints.splice(number, 1)
        this.sortCheckpoints(this.data.routeData)
        this.updateMarkers()
        this.cursor--
    }

    // Treat user photos upload
    async loadPhotos (uploadedFiles) {
        return new Promise( (resolve, reject) => {
            const acceptedFormats = ['image/jpeg', 'image/png']
            var acceptedFormatsString = acceptedFormats.join(', ')

            // Get files into an array
            var files = []
            for (var property in uploadedFiles) {
                if (Number.isInteger(parseInt(property))) files.push(uploadedFiles[property])
            }
            // Filter photos in double
            var filesLength = files.length
            var currentPhotosNumber = this.data.photos.length
            var filesInDouble = []
            this.data.photos.forEach(photo => {
                for (let i = 0 ; i < filesLength; i++) {
                    if (files[i]) {
                        if (photo.size == files[i].size && photo.name == files[i].name) {
                            filesInDouble.push(files[i].name)
                            files.splice(i, 1)
                            filesLength--
                            i--
                        }
                    }
                }
            } )
            if (filesInDouble.length > 0) {
                var filesInDoubleString = filesInDouble.join(', ')
                showResponseMessage({success: '\"' + filesInDoubleString + '\" have already been uploaded. You can\'t upload it twice.'})
            }
            // Sort files by date
            files.sort( (a, b) => {
                return a.lastModified - b.lastModified
            } )

            // Loop through each file
            var number = this.data.photos.length
            for (let i = 0; i < filesLength; i++) {

                // If the photo format is accepted
                if (acceptedFormats.includes(files[i].type)) {

                    // Extract Exif data and start image treatment
                    var blobUrl = URL.createObjectURL(files[i])
                    let img = new Image()
                    img.src = blobUrl
                    img.addEventListener('load', () => {

                        EXIF.getData(img, async() => {
                            // Extract date data
                            var exifDateTimeOriginal = EXIF.getTag(img, 'DateTimeOriginal')
                            var exifDateTime         = EXIF.getTag(img, 'DateTime')

                            // If photo has valid date data
                            if (exifDateTimeOriginal || exifDateTime) {

                                const [dateValues, timeValues] = exifDateTimeOriginal.split(' ')
                                const [year, month, day] = dateValues.split(':')
                                const [hours, minutes, seconds] = timeValues.split(':')
                                var dateOriginal = new Date(year, month - 1, day, hours, minutes, seconds)

                                // If the photo has been taken during the activity
                                if (dateOriginal.getDay() == new Date(this.data.checkpoints[0].datetime).getDay()) {

                                    // Resize and compress photo
                                    let blob = await resizeAndCompress(img, 1600, 900, 0.7)

                                    // Add photo to map instance
                                    this.data.photos.push( {
                                        blob,
                                        size: files[i].size,
                                        name: files[i].name,
                                        type: files[i].type,
                                        datetime: dateOriginal.getTime(),
                                        featured: false,
                                        number
                                    } )
                                    
                                    number++

                                    // Resolve promise after last file has been treated
                                    if (number == filesLength + currentPhotosNumber) resolve(true)

                                } else showResponseMessage({error: '\"' + files[i].name + '\" has not been taken during the activity.'})

                            } else  showResponseMessage({error: '\"' + files[i].name + '\" does not have valid time data. Please upload raw photo data taken during the activity.'})

                        } )

                    } )
                    
                } else showResponseMessage({error: '\"' + files[i].name + '\" is not of an accepted format. Please upload images from following formats : ' + acceptedFormatsString + '.'})
            }
        } )
    }

    sortPhotos () {
        // Sort photos by datetime
        this.data.photos.sort((a, b) => {
            return a.datetime - b.datetime
        } )
        // Update photos numbers
        for (let number = 0; number < this.data.photos.length; number++) this.data.photos[number].number = number
    }
    
    // Reorder photo elements within checkpoints according to date
    updatePhotos () {
        this.sortPhotos()
        this.removePhotoElements()
        this.data.photos.forEach(photo => {
            this.updatePhotoElement(photo)
        } )
    }

    // Append photo element before the next checkpoint
    updatePhotoElement (photo) {        
        const reader = new FileReader()
        reader.readAsDataURL(photo.blob)
        reader.addEventListener("load", () => {
            var dataUrl = reader.result

            // Search for closest checkpoint to append
            var closestCheckpointNumber = 0
            var closestCheckpointDatetime = 0
            this.data.checkpoints.forEach(checkpoint => {
                if (checkpoint.datetime > closestCheckpointDatetime && checkpoint.datetime < photo.datetime) {
                    if (checkpoint.number + 1 > this.data.checkpoints.length - 1) closestCheckpointNumber = checkpoint.number
                    else closestCheckpointNumber = checkpoint.number + 1
                    closestCheckpointDatetime = checkpoint.datetime
                }
            } )
            
            // Create and append elements to the DOM
            photo.thumbnailElement = document.createElement('div')
            photo.thumbnailElement.className = 'pg-ac-photo-container'
            photo.thumbnailElement.style.cursor = 'default'
            var $img = document.createElement('img')
            $img.className = 'pg-ac-photo'
            $img.src = dataUrl
            photo.thumbnailElement.appendChild($img)
            var $deleteButton = document.createElement('div')
            $deleteButton.className = 'pg-ac-close-button'
            $deleteButton.innerText = 'x'
            photo.thumbnailElement.appendChild($deleteButton)
            // If first photo of this checkpoint, append to parent, else find previous child and insert if after
            var $parent = document.querySelector('#checkpointForm' + closestCheckpointNumber + ' .pg-ac-photos-container')
            var $previousChildNumber = 0
            var $previousChild = false
            if ($parent.children.length > 0) {
                for (let i = photo.number - 1; i >= 0; i--) {
                    if (this.data.photos[i].thumbnailElement && this.data.photos[i].thumbnailElement.closest('#checkpointForm' + closestCheckpointNumber + ' .pg-ac-photos-container') == $parent) {
                        if (this.data.photos[i].number > $previousChildNumber && this.data.photos[i].number < photo.number) {
                            $previousChildNumber = this.data.photos[i].number
                            $previousChild = this.data.photos[i].thumbnailElement
                        }
                    }
                }
                if ($previousChild) $previousChild.after(photo.thumbnailElement)
                else $parent.appendChild(photo.thumbnailElement)
            } else $parent.appendChild(photo.thumbnailElement)

            // Delete photo listener
            $deleteButton.addEventListener('click', () => {
                photo.thumbnailElement.remove()
                this.data.photos.splice(photo.number, 1)
                // Update other photos number
                for (let i = 0; i < this.data.photos.length; i++) {
                    if (this.data.photos[i].number > photo.number) {
                        this.data.photos[i].number--
                    }
                }
            } )

        }, false)        
    }

    // Remove all photo elements from the DOM
    removePhotoElements () {
        document.querySelectorAll('.pg-ac-photo-container').forEach($photoContainer => $photoContainer.remove())
    }

    async saveActivity () {
        return new Promise( async (resolve, reject) => {
            
            // Remove trackpoints and photos data
            var cleanData = {}
            for (var key in this.data) {
                if (key != 'trackpoints' && key != 'photos') cleanData[key] = this.data[key]
            }
            // Remove marker data
            cleanData.checkpoints.forEach(checkpoint => {
                delete checkpoint.marker
            } )

            // Prepare photo blobs upload
            const photos = this.data.photos
            cleanData.photos = []
            photos.forEach(async (photo) => {
                cleanData.photos.push( {
                    blob: await blobToBase64(photo.blob),
                    size: photo.size,
                    name: photo.name,
                    type: photo.type,
                    datetime: photo.datetime,
                    featured: photo.featured
                } )
            })

            // Save canvas as a picture
            await this.focus(this.data.routeData)
            this.map.on('idle', () => {
                html2canvas(document.querySelector('.mapboxgl-canvas')).then( (canvas) => {
                    canvas.toBlob( async (blob) => {
                        cleanData.thumbnail = await blobToBase64(blob)
                        // Send data to server
                        ajaxJsonPostRequest (this.apiUrl, cleanData, (response) => {
                            resolve(response)
                            window.location.replace('/activities/myactivities.php')
                        } )
                    }, 'image/jpeg', 0.7)
                } )     
            } )
        } )
    }

}