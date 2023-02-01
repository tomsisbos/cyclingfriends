import Modal from "/map/class/Modal.js"
import ActivityMap from "/map/class/activity/ActivityMap.js"
import CFUtils from "/map/class/CFUtils.js"

export default class NewActivityMap extends ActivityMap {

    constructor () {
        super()
    }

    pageType = 'new'
    apiUrl = '/api/activities/save.php'
    data
    cursor = 2
    loader = {
        prepare: () => {
            this.loaderElement = document.createElement('div')
            this.loaderElement.className = 'loading-modal'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerText = 'アクティビティデータを処理しています...'
            this.loaderElement.style.cursor = 'loading'
            loaderIcon.className = 'loading-text'
            this.loaderElement.appendChild(loaderIcon)
        },
        start: () => this.loaderContainer.appendChild(this.loaderElement),
        stop: () => this.loaderElement.remove()
    }

    // Parse file data and store it inside map instance
    async importDataFromGpx (gpx) {
        return new Promise(async (resolve, reject) => {

            // Build trackpoints and routeCoords
            const track = gpx.tracks[0]
            const segment = track.segments[0]
            const trkpt = segment.points

            var trackpoints = []
            var routeCoords = []
            var routeTime = []
            console.log(gpx)
            for (let i = 0; i < trkpt.length; i++) {
                if (trkpt[i].time == null) return resolve({error: 'このファイルにはタイムデータが付随されていないため、アクティビティとして保存することが出来ません。'})
                else {
                    var date = new Date(trkpt[i].time.date)
                    let trackpoint = {
                        lngLat: {
                            lng: trkpt[i].longitude,
                            lat: trkpt[i].latitude
                        },
                        elevation: trkpt[i].elevation,
                        time: date.setMinutes(date.getMinutes() - date.getTimezoneOffset()) // Needs to add the timezone difference to be correct
                    }
                    routeCoords.push([trkpt[i].longitude, trkpt[i].latitude])
                    routeTime.push(trackpoint.time)
                    if (trkpt[i].extensions) {
                        if (trkpt[i].extensions.trackPointExtension.aTemp) trackpoint.temperature = trkpt[i].extensions.trackPointExtension.aTemp
                        if (trkpt[i].extensions.trackPointExtension.cad) trackpoint.cadence = trkpt[i].extensions.trackPointExtension.cad
                        if (trkpt[i].extensions.unsupported.power) trackpoint.power = parseInt(trkpt[i].extensions.unsupported.power)
                    }
                    trackpoints.push(trackpoint)
                }
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
                    if (distance > 0.015) duration_running += (trackpoints[i].time - trackpoints[i - precision].time)
                    if (seconds > precision) duration_running -= (seconds - precision) * 1000 // Substact auto pause
                }
            }

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
            this.data = { routeData }

            // Build temperature
            var hasTemperatureData = false
            for (let j = 0; !('temperature' in trackpoints[j]) || j < trackpoints.length - 1; j++) {
                if (trackpoints[j].temperature) hasTemperatureData = true
            }
            if (hasTemperatureData) {
                var sumTemperature = 0
                var minTemperature = 100
                var maxTemperature = -100
                for (let i = 0; i < trackpoints.length; i++) {
                    if (trackpoints[i].temperature) sumTemperature += parseInt(trackpoints[i].temperature)
                    if (trackpoints[i].temperature < minTemperature) minTemperature = parseInt(trackpoints[i].temperature)
                    if (trackpoints[i].temperature > maxTemperature) maxTemperature = parseInt(trackpoints[i].temperature)
                }
                var avgTemperature = Math.floor(sumTemperature / trackpoints.length * 10) / 10
            }

            // Build duration
            const endDate = trackpoints[trackpoints.length - 1].time
            const startDate = trackpoints[0].time
            var duration = getDurationFromTimestamp(endDate - startDate)
            // If no time data, display an error message
            if (endDate - startDate <= 60) return resolve({error: 'このファイルにはタイムデータが付随されていないため、アクティビティとして保存することが出来ません。'})
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
                    mkpoints: await this.loadCloseMkpoints(1, {displayOnMap: false, generateProfile: false, getFileBlob: false}),
                    segments: await this.getFittingSegments(),
                    photos: [],
                    trackpoints
                }
                resolve({success: true})
            }
        } )
    }

    // Parse file data and store it inside map instance
    async importDataFromFit (fit) {
        return new Promise(async (resolve, reject) => {
            console.log(fit)

            // Build trackpoints and routeCoords
            const record = fit.record
            var trackpoints = []
            var routeCoords = []
            var routeTime = []
            for (let i = 0; i < record.position_long.length; i++) {
                let trackpoint = {
                    lngLat: {
                        lng: record.position_long[i],
                        lat: record.position_lat[i],
                    },
                    elevation: record.altitude[i],
                    time: record.timestamp[i] * 1000
                }
                if (record.temperature) trackpoint.temperature = record.temperature[i]
                if (record.cadence) trackpoint.cadence = record.cadence[i]
                if (record.power) trackpoint.power = record.power[i]
                trackpoints.push(trackpoint)
                routeCoords.push([record.position_long[i], record.position_lat[i]])
                routeTime.push(record.timestamp[i] * 1000)
            }

            console.log(trackpoints)

            // Build max altitude
            const session = fit.session
            var altitude_max = record.altitude.reduce((a, b) => Math.max(a, b), -Infinity)
            // Build max speed
            var speed_max = Math.floor(session.max_speed * 10) / 10
            // Build max slope
            var slope_max = 0
            const precision = 10 // Calcul interval in seconds
            for (let i = 0; i < routeCoords.length; i += precision) {
                if (routeCoords[i - precision]) {
                    // Build distance
                    let distance = 0
                    for (let j = 0; j < precision; j++) distance += turf.distance(routeCoords[i - j], routeCoords[i - j - 1])
                    // Build max slope
                    let elevation = parseInt(trackpoints[i].elevation) - parseInt(trackpoints[i - precision].elevation)
                    if (distance * 1000 > 10) var slope = elevation * 100 / (distance * 1000) // Prevent inaccurate calculation caused by too short section distance
                    if (slope > slope_max) slope_max = Math.round(slope * 10) / 10
                }
            }
            // Build time running
            var duration_running = session.total_timer_time * 1000

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
            this.data = { routeData }

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
            const endDate = trackpoints[trackpoints.length - 1].time
            const startDate = trackpoints[0].time
            var duration = getDurationFromTimestamp(endDate - startDate)
            // If no time data, display an error message
            if (endDate - startDate <= 60) resolve({error: 'このファイルにはタイムデータが付随されていないため、アクティビティとして保存することが出来ません。'})
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
                    title: startPoint.geolocation.city + ' ride',
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
                    mkpoints: await this.loadCloseMkpoints(1, {displayOnMap: false}),
                    segments: await this.getFittingSegments(),
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
        $title.addEventListener('change', () => this.data.title = $title.value)
        $start.innerHTML = '<strong>スタート : </strong>' + this.data.checkpoints[0].geolocation.city + ' (' + this.data.checkpoints[0].geolocation.prefecture + ')'
        $goal.innerHTML = '<strong>ゴール : </strong>' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.city + ' (' + this.data.checkpoints[this.data.checkpoints.length - 1].geolocation.prefecture + ')'
        $distance.innerHTML = '<strong>距離 : </strong>' + this.data.distance + 'km'
        $duration.innerHTML = '<strong>時間 : </strong>' + getFormattedDurationFromTimestamp(this.data.trackpoints[this.data.trackpoints.length - 1].time - this.data.trackpoints[0].time)
        $elevation.innerHTML = '<strong>獲得標高 : </strong>' + this.data.elevation + 'm'
        if (this.data.temperature.min) $minTemperature.innerHTML = '<strong>最低気温 : </strong>' + this.data.temperature.min + '°C'
        if (this.data.temperature.avg) $avgTemperature.innerHTML = '<strong>平均気温 : </strong>' + this.data.temperature.avg + '°C'
        if (this.data.temperature.max) $maxTemperature.innerHTML = '<strong>最高気温 : </strong>' + this.data.temperature.max + '°C'
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
                $name.placeholder = '名前...'
                if (checkpoint.name) $name.value = checkpoint.name
                var $properties = document.createElement('div')
                $properties.className = 'new-ac-checkpoint-properties form-control-plaintext'
                var $distance = document.createElement('div')
                $distance.innerHTML = '<strong>km ' + (Math.ceil(checkpoint.distance * 10) / 10) + '</strong>'
                var $datetime = document.createElement('div')
                $datetime.innerHTML = '\u00a0- ' + getFormattedDurationFromTimestamp(checkpoint.datetime - this.data.checkpoints[0].datetime)
                if (checkpoint.type == 'Start' || checkpoint.type == 'Goal') {
                    var $type = document.createElement('div')
                    if (checkpoint.type) $type.innerHTML = checkpoint.type
                    $type.setAttribute('readonly', true)
                    $type.className = 'form-control-plaintext'
                } else {
                    var $type = buildCheckpointSelectType(checkpoint.type)
                    $type.className = 'form-select'
                }
                var $story = document.createElement('textarea')
                if (checkpoint.story) $story.innerText = checkpoint.story
                $story.className = 'form-control'
                $story.placeholder = 'ストーリー...'
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

        function buildCheckpointSelectType (type) {
            var $type = document.createElement('select')
            // Landscape
            let $landscape = document.createElement('option')
            $landscape.value = 'Landscape'
            $landscape.text = '景色'
            if (type == 'Landscape') $landscape.setAttribute('selected', true)
            $type.add($landscape)
            // Break
            let $break = document.createElement('option')
            $break.value = 'Break'
            $break.text = '休憩'
            if (type == 'Break') $break.setAttribute('selected', true)
            $type.add($break)
            // Restaurant
            let $restaurant = document.createElement('option')
            $restaurant.value = 'Restaurant'
            $restaurant.text = '食事'
            if (type == 'Restaurant') $restaurant.setAttribute('selected', true)
            $type.add($restaurant)
            // Cafe
            let $cafe = document.createElement('option')
            $cafe.value = 'Cafe'
            $cafe.text = 'カフェ'
            if (type == 'Cafe') $cafe.setAttribute('selected', true)
            $type.add($cafe)
            // Attraction
            let $attraction = document.createElement('option')
            $attraction.value = 'Attraction'
            $attraction.text = '情報'
            if (type == 'Attraction') $attraction.setAttribute('selected', true)
            $type.add($attraction)
            // Event
            let $event = document.createElement('option')
            $event.value = 'Event'
            $event.text = '出来事'
            if (type == 'Event') $event.setAttribute('selected', true)
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
        this.updatePhotos()
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
                showResponseMessage({success: '\"' + filesInDoubleString + '\"は既にアップロードされています。再度アップロードすることが出来ません。'})
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
                                if (this.data.checkpoints[0].datetime.date) var checkpointDatetime = new Date(this.data.checkpoints[0].datetime.date)
                                else var checkpointDatetime = new Date(this.data.checkpoints[0].datetime)
                                if (dateOriginal.getMonth() == checkpointDatetime.getMonth() && dateOriginal.getDay() == checkpointDatetime.getDay()) {

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
                                    console.log(this.data.photos)
                                    
                                    number++

                                    // Resolve promise after last file has been treated
                                    if (number == filesLength + currentPhotosNumber) resolve(true)

                                } else {
                                    showResponseMessage({error: '\"' + files[i].name + '\"はアクティビティ中に撮影された写真ではありません。'})
                                    filesLength--
                                }

                            } else {
                                showResponseMessage({error: '\"' + files[i].name + '\"にはタイムデータが付随されていません。未加工のファイルをアップロードしてください。'})
                                filesLength--
                            }

                        } )

                    } )
                    
                } else {
                    showResponseMessage({error: '\"' + files[i].name + '\"のファイル形式に対応していません。対応しているファイル形式は次の通り：' + acceptedFormatsString + '.'})
                    filesLength--
                }
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
    async updatePhotos () {
        (async () => {
            return new Promise(async (resolve, reject) => {
                this.sortPhotos()
                this.removePhotoElements()
                for (let i = 0; i < this.data.photos.length; i++) {
                    await this.updatePhotoElement(this.data.photos[i])
                    if (i == this.data.photos.length - 1) resolve(true)
                }
            })}
        ) ().then( () => {
            this.highlightFeaturedPhoto()
        } )
    }

    setFeatured (photoToFeature) {
        if (!photoToFeature.featured) {
            this.data.photos.forEach((photo) => {
                if (photo == photoToFeature) photo.featured = true
                else photo.featured = false
            } )
        } else photoToFeature.featured = false
        this.highlightFeaturedPhoto()
    }

    // Append photo element before the next checkpoint
    async updatePhotoElement (photo) {

        return new Promise(async (resolve, reject) => {

            if (photo.blob instanceof Blob) var dataUrl = await getDataURLFromBlob(photo.blob)
            else var dataUrl = 'data:' + photo.type + ';base64,' + photo.blob

            // Search for closest checkpoint to append
            var closestCheckpointNumber = this.findCheckpointNumberToAppend(photo)
            
            // Create and append elements to the DOM
            photo.$thumbnail = document.createElement('div')
            photo.$thumbnail.className = 'pg-ac-photo-container'
            photo.$thumbnail.style.cursor = 'default'
            var $img = document.createElement('img')
            $img.className = 'pg-ac-photo'
            $img.src = dataUrl
            // Modal on thumbnail click
            var modal = new Modal(dataUrl)
            document.body.appendChild(modal.element)
            $img.addEventListener('click', () => modal.open())
            photo.$thumbnail.appendChild($img)
            // Delete button
            var $deleteButton = document.createElement('div')
            $deleteButton.className = 'pg-ac-close-button'
            $deleteButton.innerText = 'x'
            $deleteButton.title = '写真を削除する'
            photo.$thumbnail.appendChild($deleteButton)
            // Feature button
            var $featureButton = document.createElement('div')
            $featureButton.className = 'pg-ac-feature-button'
            $featureButton.innerHTML = '<span class="iconify" data-icon="mdi:feature-highlight"></span>'
            $featureButton.title = 'ハイライト写真に選定する'
            photo.$thumbnail.appendChild($featureButton)
            // Create mkpoint button
            var $createMkpointButton = document.createElement('div')
            $createMkpointButton.className = 'pg-ac-createmkpoint-button'
            $createMkpointButton.innerHTML = '<span class="iconify" data-icon="material-symbols:add-location-alt"></span>'
            $createMkpointButton.title = 'この写真を元に絶景スポットを新規作成する'
            photo.$thumbnail.appendChild($createMkpointButton)
            // If first photo of this checkpoint, append to parent, else find previous child and insert if after
            var $parent = document.querySelector('#checkpointForm' + closestCheckpointNumber + ' .pg-ac-photos-container')
            var $previousChildNumber = 0
            var $previousChild = false
            if ($parent.children.length > 0) {
                for (let i = photo.number - 1; i >= 0; i--) {
                    if (this.data.photos[i].$thumbnail && this.data.photos[i].$thumbnail.closest('#checkpointForm' + closestCheckpointNumber + ' .pg-ac-photos-container') == $parent) {
                        if (this.data.photos[i].number > $previousChildNumber && this.data.photos[i].number < photo.number) {
                            $previousChildNumber = this.data.photos[i].number
                            $previousChild = this.data.photos[i].$thumbnail
                        }
                    }
                }
                if ($previousChild) $previousChild.after(photo.$thumbnail)
                else $parent.appendChild(photo.$thumbnail)
            } else $parent.appendChild(photo.$thumbnail)

            // Set as featured photo listener
            $featureButton.addEventListener('click', () => {
                this.setFeatured(photo)
            } )

            // Create mkpoint listener
            $createMkpointButton.addEventListener('click', () => {
                // Initialize list if necessary
                if (!this.data.mkpointsToCreate) this.data.mkpointsToCreate = []
                // If no similar entry exists yet, create it and highlight thumbnail
                if (!this.data.mkpointsToCreate.includes(photo)) {
                    this.data.mkpointsToCreate.push(photo)
                    photo.$thumbnail.firstChild.classList.add('admin-marker')
                    photo.$thumbnail.querySelector('.pg-ac-createmkpoint-button').style.color = 'yellow'
                // Else, remove entry in map instance data and set thumbnail back to default 
                } else {
                    var key = this.data.mkpointsToCreate.find(key => key == photo)
                    this.data.mkpointsToCreate.splice(key, 1)
                    photo.$thumbnail.firstChild.classList.remove('admin-marker')
                    photo.$thumbnail.querySelector('.pg-ac-createmkpoint-button').style.color = 'white'
                }
                console.log(this.data.mkpointsToCreate)
            } )

            // Delete photo listener
            $deleteButton.addEventListener('click', () => {
                photo.$thumbnail.remove()
                this.data.photos.splice(photo.number, 1)
                // Update other photos number
                for (let i = 0; i < this.data.photos.length; i++) {
                    if (this.data.photos[i].number > photo.number) {
                        this.data.photos[i].number--
                    }
                }
            } )

            resolve(true)
        })
    }

    findCheckpointNumberToAppend (photo) {
        var closestCheckpointNumber = 0
        var closestCheckpointDatetime = 0
        this.data.checkpoints.forEach(checkpoint => {
            if (checkpoint.datetime > closestCheckpointDatetime && checkpoint.datetime < photo.datetime) {
                if (checkpoint.number + 1 > this.data.checkpoints.length - 1) closestCheckpointNumber = checkpoint.number
                else closestCheckpointNumber = checkpoint.number + 1
                closestCheckpointDatetime = checkpoint.datetime
            }
        } )
        return closestCheckpointNumber
    }

    // Automatically highlight featured photo
    highlightFeaturedPhoto () {
        var isSetFeatured = false
        this.data.photos.forEach(photo => {
            if (photo.featured) {
                photo.$thumbnail.firstChild.classList.add('selected-marker')
                photo.$thumbnail.querySelector('.pg-ac-feature-button').style.color = "#ff5555"
                isSetFeatured = true
            } else {
                photo.$thumbnail.firstChild.classList.remove('selected-marker')
                photo.$thumbnail.querySelector('.pg-ac-feature-button').style.color = "white"
            }
        } )
    }

    // Remove all photo elements from the DOM
    removePhotoElements () {
        document.querySelectorAll('.pg-ac-photo-container').forEach($photoContainer => $photoContainer.remove())
    }

    async checkForCloseMkpoints () {
        return new Promise(async (resolve, reject) => {
            // Compare all close mkpoints to all uploaded photos location and store similar data
            var photosToAsk = []
            this.data.mkpoints.forEach(mkpoint => {
                this.data.photos.forEach(photo => {
                    var photoLocation = {lng: this.getPhotoLocation(photo)[0], lat: this.getPhotoLocation(photo)[1]}
                    if (CFUtils.compareCoords({lng: mkpoint.lng, lat: mkpoint.lat}, photoLocation, 3)) {
                        console.log(photo.name + ' could be added to ' + mkpoint.name + ' at a decimal level of 3.')
                        photosToAsk.push({photo, mkpoint})
                        // If any photo close to an existing mkpoint have been added to the create mkpoints list, discard it
                        if (this.data.mkpointsToCreate) for (let i = 0; i < this.data.mkpointsToCreate.length; i++) {
                            if (this.data.mkpointsToCreate[i].size == photo.size) {
                                this.data.mkpointsToCreate.splice(i, 1)
                                i--
                                showResponseMessage({'error': photo.name + 'の位置が既存の絶景スポット「' + mkpoint.name + '」と一致しているため、新規の絶景スポットを作成できません。その代わり、写真として「' + mkpoint.name + '」に追加してください。'})
                            }
                        }
                    }
                } )
            } )
            console.log(photosToAsk)
            if (photosToAsk.length > 0) var photosToShare = await this.openSelectPhotosToShareModal(photosToAsk)
            else var photosToShare = []
            console.log(photosToShare)
            resolve(photosToShare)
        } )
    }

    async openSelectPhotosToShareModal (photosToAsk) {
        return new Promise ((resolve, reject) => {

            // Build window structure
            var modal = document.createElement('div')
            modal.classList.add('modal', 'd-block')
            document.querySelector('body').appendChild(modal)
            var confirmationPopup = document.createElement('div')
            confirmationPopup.classList.add('popup', 'fullscreen-popup')
            modal.appendChild(confirmationPopup)
            confirmationPopup.innerHTML = `下記の写真は、絶景スポットに指定されている場所で撮影されました。絶景スポットの公開写真に追加し、コミュニティと共有しますか？<br>
            (!) 写真の公開にはルールがあります。<a>こちら</a>で確認してください。`
            var $entriesContainer = document.createElement('div')
            $entriesContainer.className = 'new-ac-entries-container'
            confirmationPopup.appendChild($entriesContainer)

            // Build cancel button
            var $cancelButton = document.createElement('button')
            $cancelButton.className = 'btn button bg-danger text-white'
            $cancelButton.innerText = '戻る'
            confirmationPopup.appendChild($cancelButton)
            $cancelButton.addEventListener('click', () => modal.remove())

            photosToAsk.forEach(async (entry) => {

                // Build photoToAsk elements
                var dataUrl = await getDataURLFromBlob(entry.photo.blob)
                var $entry = document.createElement('div')
                $entry.dataset.photoname = entry.photo.name
                $entry.dataset.mkpointid = entry.mkpoint.id
                $entry.innerHTML = `
                    <div class="new-ac-window-photo">
                        <img src="` + dataUrl + `" />
                        <div class="new-ac-window-distance">`
                            + (Math.ceil(entry.mkpoint.distance * 10) / 10) + `km
                        </div>
                    </div>
                    <div class="new-ac-window-mkpoint-infos">`
                        + entry.mkpoint.name + `
                    </div>
                    <div class="d-flex justify-content-between"><div class="mp-button bg-darkgreen text-white js-yes">はい</div><div class="mp-button bg-darkred text-white js-no">いいえ</div></div>
                `
                $entriesContainer.appendChild($entry)

                var $yes = $entry.querySelector('.js-yes')
                var $no = $entry.querySelector('.js-no')
                // On click on "Yes" button, close the popup and return true
                $yes.addEventListener('click', () => {
                    entry.answer = 'keep'
                    this.styleButtons($yes, $no)
                    if (isEverythingAnswered(photosToAsk)) {
                        treatAnswers(photosToAsk)
                    }
                } )
                // On click on "No" button, return false and close the popup
                $no.addEventListener('click', () => {
                    entry.answer = 'discard'
                    this.styleButtons($no, $yes)
                    if (isEverythingAnswered(photosToAsk)) {
                        treatAnswers(photosToAsk)
                    }
                } )
            } )

            function isEverythingAnswered (photosToAsk) {
                var isEverythingAnswered = true
                photosToAsk.forEach(tempEntry => {
                    if (!tempEntry.answer) isEverythingAnswered = false
                })
                if (isEverythingAnswered) return true
                else return false
            }

            function treatAnswers (photosToAsk) {
                modal.remove()
                var photosToShare = []
                photosToAsk.forEach(finalEntry => {
                    if (finalEntry.answer == 'keep') photosToShare.push({photo_name: finalEntry.photo.name, mkpoint_id: finalEntry.mkpoint.id})
                } )
                resolve(photosToShare)
            }
        } )
    }

    styleButtons ($clickedButton, $otherButton) {
        console.log($clickedButton)
        console.log($otherButton)
        if (!$clickedButton.classList.contains('new-ac-btn-kept')) $clickedButton.classList.add('new-ac-btn-kept')
        if ($clickedButton.classList.contains('new-ac-btn-discarded')) $clickedButton.classList.remove('new-ac-btn-discarded')
        if ($otherButton.classList.contains('new-ac-btn-kept')) $otherButton.classList.remove('new-ac-btn-kept')
        if (!$otherButton.classList.contains('new-ac-btn-discarded')) $otherButton.classList.add('new-ac-btn-discarded')
    }

    async createMkpoints () {
        return new Promise(async (resolve, reject) => {
            // Store close photos that could also be added
            for (let i = 0; i < this.data.mkpointsToCreate.length; i++) {
                this.data.mkpointsToCreate[i].closePhotos = []
                var currentPhotoLocation = {lng: this.getPhotoLocation(this.data.mkpointsToCreate[i])[0], lat: this.getPhotoLocation(this.data.mkpointsToCreate[i])[1]}
                this.data.photos.forEach(photo => {
                    var photoLocation = {lng: this.getPhotoLocation(photo)[0], lat: this.getPhotoLocation(photo)[1]}
                    if (CFUtils.compareCoords(currentPhotoLocation, photoLocation, 2) && this.data.mkpointsToCreate[i].name != photo.name) this.data.mkpointsToCreate[i].closePhotos.push(photo)
                } )
            }
            // Open modal and get user input data
            console.log(this.data.mkpointsToCreate)
            var mkpointsToCreate = await this.openCreateMkpointsModal()
            resolve(mkpointsToCreate)
        } )
    }

    async openCreateMkpointsModal () {
        return new Promise ((resolve, reject) => {
            
            // Build window structure
            var modal = document.createElement('div')
            modal.classList.add('modal', 'd-block')
            document.querySelector('body').appendChild(modal)/*
            modal.addEventListener('mousedown', (e) => {
                if (e.target === modal) modal.remove()
            } )*/
            var confirmationPopup = document.createElement('div')
            confirmationPopup.classList.add('popup', 'fullscreen-popup')
            modal.appendChild(confirmationPopup)
            confirmationPopup.innerHTML = `
            (!) 写真の公開にはルールがあります。<a>こちら</a>で確認してください。`
            var $entriesContainer = document.createElement('div')
            $entriesContainer.className = 'new-ac-entries-container'
            confirmationPopup.appendChild($entriesContainer)

            // Build each mkpoint element
            this.data.mkpointsToCreate.forEach(async (entry) => {
                console.log(this.data.routeData)
                var distance = turf.length(turf.lineSlice(this.data.routeData.geometry.coordinates[0], this.getPhotoLocation(entry), this.data.routeData))
                var content = ''
                var mkpointElement = document.createElement('div')
                mkpointElement.id = 'form' + entry.number
                // Build tag checkboxes
                var $tags = '<div class="js-tags">'
                this.tags.forEach(tag => {
                    $tags += `
                        <div class="mp-checkbox">
                            <input type="checkbox" data-name="` + tag + `" id="tag` + tag + entry.number + `" class="js-segment-tag" />
                            <label for="tag` + tag + entry.number + `">` + CFUtils.getTagString(tag) + `</label>
                        </div>
                    `
                } )
                $tags += '</div>'
                // Build photos section
                var dataUrl = await getDataURLFromBlob(entry.blob)
                content += `
                    <div class="new-ac-window-photo">
                        <img src="` + dataUrl + `" />
                        <div class="new-ac-window-distance">`
                            + (Math.ceil(distance * 10) / 10) + `km
                        </div>
                    </div>
                `
                    
                if (entry.closePhotos.length > 0) {
                    content += '次の写真も、この絶景スポットに追加することができます。追加しますか？<div class="new-ac-window-other-photos-container">'
                    content += await buildOtherPhotosElements(entry.closePhotos)
                    content += '</div>'
                }
                // Build mkpoint form element
                content += `
                    <div class="popup-content">
                        <strong>タイトル :</strong>
                        <input type="text" class="admin-field js-mkpoint-name"/>
                        <strong>紹介文 :</strong>
                        <textarea class="admin-field js-mkpoint-description"></textarea>
                    </div>`
                    + $tags + `
                `
                mkpointElement.innerHTML = content
                $entriesContainer.appendChild(mkpointElement)

                // Append listeners to other photos buttons
                if (entry.closePhotos.length > 0) {
                    for (let i = 0; i < entry.closePhotos.length; i++) {
                        var $photo = $entriesContainer.querySelector('#otherPhoto' + entry.closePhotos[i].number)
                        var $yes = $photo.querySelector('.js-yes')
                        var $no = $photo.querySelector('.js-no')
                        console.log($yes)
                        console.log($no)
                        // On click on "Yes" button, close the popup and return true
                        $yes.addEventListener('click', (e) => {
                            entry.closePhotos[i].answer = 'keep'
                            var $clickedButton = e.target
                            var $otherButton = e.target.closest('.new-ac-window-photo-element').querySelector('.js-no')
                            this.styleButtons($clickedButton, $otherButton)
                        } )
                        // On click on "No" button, return false and close the popup
                        $no.addEventListener('click', (e) => {
                            entry.closePhotos[i].answer = 'discard'
                            var $clickedButton = e.target
                            var $otherButton = e.target.closest('.new-ac-window-photo-element').querySelector('.js-yes')
                            this.styleButtons($clickedButton, $otherButton)
                        } )
                    }
                }
            } )

            // Build cancel and validate button
            var $cancelButton = document.createElement('button')
            $cancelButton.className = 'btn button mx-2 bg-danger text-white'
            $cancelButton.innerText = '戻る'
            confirmationPopup.appendChild($cancelButton)
            $cancelButton.addEventListener('click', () => modal.remove())
            var $validateButton = document.createElement('button')
            $validateButton.className = 'btn button mx-2 bg-primary text-white'
            $validateButton.innerText = '確定'
            confirmationPopup.appendChild($validateButton)
            $validateButton.addEventListener('click', () => {
                var mkpointsToCreate = []
                var filled = true
                this.data.mkpointsToCreate.forEach(async (entry) => {
                    console.log('form' + entry.number)
                    var $mkpointForm = document.querySelector('#form' + entry.number)
                    console.log($mkpointForm)
                    var name = $mkpointForm.querySelector('.js-mkpoint-name').value
                    var description = $mkpointForm.querySelector('.js-mkpoint-description').value
                    var tags = []
                    $mkpointForm.querySelectorAll('.js-segment-tag').forEach($tagInput => {
                        if ($tagInput.checked) tags.push($tagInput.dataset.name)
                    } )
                    var photos = [{
                        size: entry.size,
                        name: entry.name,
                        type: entry.type
                    }]
                    entry.closePhotos.forEach(closePhoto => {
                        if (closePhoto.answer && closePhoto.answer == 'keep') {
                            photos.push({
                                size: closePhoto.size,
                                name: closePhoto.name,
                                type: closePhoto.type
                            })
                        }
                    })

                    console.log(this)
                    var lngLat = {lng: this.getPhotoLocation(entry)[0], lat: this.getPhotoLocation(entry)[1]}
                    var location = await this.getLocation(lngLat)
                    mkpointsToCreate.push( {
                        name,
                        description,
                        tags,
                        lngLat,
                        city: location.city,
                        prefecture: location.prefecture,
                        elevation: Math.floor(this.map.queryTerrainElevation(lngLat)),
                        photos
                    } )
                    if (name == '' || description == '') filled = false
                } )
                if (filled) resolve(mkpointsToCreate)
                else showResponseMessage({'error': '絶景スポットにはタイトルと紹介文が必要です。必要に応じて、<a>絶景スポットの共有ルール</a>をご確認ください。'}, {element: document.querySelector('.popup')})
            } )

            async function buildPhotoElement (photo) {
                return new Promise(async (resolve, reject) => {
                    var dataUrl = await getDataURLFromBlob(photo.blob)
                    resolve(`
                    <div class="new-ac-window-photo-element" id="otherPhoto` + photo.number + `">
                        <div class="new-ac-window-photo">
                            <img src="` + dataUrl + `" />
                        </div>
                        <div class="d-flex justify-content-between"><div class="mp-button bg-darkgreen text-white js-yes">はい</div><div class="mp-button bg-darkred text-white js-no">いいえ</div></div>
                    </div>
                    `)
                } )
            }

            async function buildOtherPhotosElements (photos) {
                return new Promise(async (resolve, reject) => {
                    var content = ''
                    for (let i = 0; i < photos.length; i++) {
                        var photoElement = await buildPhotoElement(photos[i])
                        content += photoElement
                    }
                    resolve(content)
                } )
            }

        } )
    }

    async saveActivity (mkpointPhotos = null, mkpointsToCreate = null) {
        return new Promise(async (resolve, reject) => {
            
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
                    lng: this.getPhotoLocation(photo)[0],
                    lat: this.getPhotoLocation(photo)[1],
                    datetime: photo.datetime,
                    featured: photo.featured
                } )
            })

            // If photos need to be added to a mkpoint, append info data
            if (mkpointPhotos) cleanData.mkpointPhotos = mkpointPhotos
            
            // If mkpoints need to be created, append data
            if (mkpointsToCreate) cleanData.mkpointsToCreate = mkpointsToCreate

            // Save canvas as a picture
            this.map.once('idle', () => {
                html2canvas(document.querySelector('.mapboxgl-canvas')).then( (canvas) => {
                    canvas.toBlob( async (blob) => {
                        cleanData.thumbnail = await blobToBase64(blob)
                        console.log(cleanData)
                        // Send data to server
                        ajaxJsonPostRequest (this.apiUrl, cleanData, (response) => {
                            resolve(response)
                            window.location.replace('/' + this.session.login + '/activities')
                        }, this.loader)
                    }, 'image/jpeg', 0.7)
                } )     
            } )
            var {lng, lat} = this.map.getCenter()
            this.map.setCenter({lng: lng + 0.01, lat: lat + 0.01})
            await this.focus(this.data.routeData)
        } )
    }

}