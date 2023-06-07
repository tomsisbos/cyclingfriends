import Modal from "/class/Modal.js"
import ActivityMap from "/class/maps/activity/ActivityMap.js"
import CFUtils from "/class/utils/CFUtils.js"
import FadeLoader from "/class/loaders/FadeLoader.js"

export default class NewActivityMap extends ActivityMap {

    constructor () {
        super()
    }

    pageType = 'new'
    apiUrl = '/api/activities/save.php'
    activityData
    routeData
    storyData
    cursor = 2

    // Load activity data from parsed file
    async loadActivityData (activityData) {

        this.activityData = activityData

        const coordinates = activityData.linestring.coordinates
        const trackpoints = activityData.linestring.trackpoints

        console.log(activityData)

        // Build route geojson
        this.routeData = turf.lineString(coordinates.map((lngLat) => [lngLat.lng, lngLat.lat]))
        this.routeData.properties.time = trackpoints.map((trackpoint) => trackpoint.time)

        // Build start and end checkpoints
        var checkpoints = []
        var startPoint = {
            name: 'Start',
            type: 'Start',
            story: '',
            number: 0,
            lngLat: coordinates[0],
            datetime: trackpoints[0].time,
            geolocation: activityData.summary.startplace,
            elevation: trackpoints[0].elevation,
            distance: 0,
            temperature: trackpoints[0].temperature
        }
        var goalPoint = {
            name: 'Goal',
            type: 'Goal',
            story: '',
            number: 1,
            lngLat: coordinates[coordinates.length - 1],
            datetime: trackpoints[trackpoints.length - 1].time,
            geolocation: activityData.summary.goalplace,
            elevation: trackpoints[trackpoints.length - 1].elevation,
            distance: trackpoints[trackpoints.length - 1].distance,
            temperature: trackpoints[trackpoints.length - 1].temperature
        }
        checkpoints.push(startPoint)
        checkpoints.push(goalPoint)

        // Build data
        this.data = {
            title: activityData.summary.title,
            bike_id: document.querySelector('#selectBikes').value,
            privacy: document.querySelector('#selectPrivacy').value,
            checkpoints,
            photos: []
        }

        // Append necessary data to sceneries
        activityData.sceneries.forEach(scenery => {
            var coord = [scenery.lngLat.lng, scenery.lngLat.lat]
            var closestPoint = CFUtils.replaceOnRoute(coord, this.routeData)
            scenery.distance = CFUtils.findDistanceWithTwins(this.routeData, {lng: closestPoint[0], lat: closestPoint[1]}).distance
            scenery.remoteness = turf.pointToLineDistance(coord, this.routeData)
            if (scenery.remoteness < 0,3) scenery.on_route = true
        })
        this.data.sceneries = activityData.sceneries
    }

    populateForm () {
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
        if (this.activityData.summary.title != $title.value) $title.value = this.activityData.summary.title
        $title.addEventListener('change', () => this.data.title = $title.value)
        $start.innerHTML = '<strong>スタート : </strong>' + this.activityData.summary.startplace.city + ' (' + this.activityData.summary.startplace.prefecture + ')'
        $goal.innerHTML = '<strong>ゴール : </strong>' + this.activityData.summary.goalplace.city + ' (' + this.activityData.summary.goalplace.prefecture + ')'
        $distance.innerHTML = '<strong>距離 : </strong>' + (Math.round(this.activityData.summary.distance * 10) / 10) + 'km'
        $duration.innerHTML = '<strong>時間 : </strong>' + this.activityData.summary.duration.h + ' h ' + this.activityData.summary.duration.m
        $elevation.innerHTML = '<strong>獲得標高 : </strong>' + Math.round(this.activityData.summary.positive_elevation) + 'm'
        if (this.activityData.summary.temperature_min) $minTemperature.innerHTML = '<strong>最低気温 : </strong>' + this.activityData.summary.temperature_min + '°C'
        if (this.activityData.summary.temperature_avg) $avgTemperature.innerHTML = '<strong>平均気温 : </strong>' + (Math.round(this.activityData.summary.temperature_avg * 10) / 10) + '°C'
        if (this.activityData.summary.temperature_max) $maxTemperature.innerHTML = '<strong>最高気温 : </strong>' + this.activityData.summary.temperature_max + '°C'
        this.updateCheckpointForms()
    }

    clearForm () {
        document.querySelector('#divCheckpoints').innerHTML = ''
    }

    updateCheckpointForms () {

        var $checkpoints = document.querySelector('#divCheckpoints')

        // Sort by distance
        this.data.checkpoints.sort((a, b) => {
            return a.distance - b.distance
        })

        // Build elements
        this.data.checkpoints.forEach((checkpoint) => {
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
                $properties.appendChild($distance)
                $properties.appendChild($datetime)
                $topline.appendChild($name)
                $topline.appendChild($properties)
                $topline.appendChild($type)
                checkpoint.form.appendChild($photosContainer)
                checkpoint.form.appendChild($topline)
                if (checkpoint.type != 'Goal') { // Don't build story field for goal
                    var $story = document.createElement('textarea')
                    if (checkpoint.story) $story.innerText = checkpoint.story
                    $story.className = 'form-control'
                    $story.placeholder = 'ストーリー...'
                    checkpoint.form.appendChild($story)
                }
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
                if ($story) $story.addEventListener('change', e => checkpoint.story = e.target.value)
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
    async removeOnClick (e) {
        var number = parseInt(e.target.innerHTML)
        this.data.checkpoints[number].marker.remove()
        this.data.checkpoints[number].form.remove()
        this.data.checkpoints.splice(number, 1)
        await this.sortCheckpoints()
        this.updateMarkers()
        this.updatePhotos()
        this.cursor--
    }

    // Treat user photos upload
    async loadPhotos (uploadedFiles) {
        return new Promise(async (resolve, reject) => {
            const acceptedFormats = ['jpg', 'jpeg', 'png', 'heic']
            var acceptedFormatsString = acceptedFormats.join(', ')
            
            // Start loader
            var loader = new FadeLoader('写真を処理中...')
            loader.start()

            // Get files into an array
            var files = []
            for (var property in uploadedFiles) {
                if (Number.isInteger(parseInt(property))) files.push(uploadedFiles[property])
            }

            // Filter photos in double
            loader.setText('複数回アップロードされた写真がないか確認中...')
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
            loader.setText('写真を整理中...')
            files.sort( (a, b) => {
                return a.lastModified - b.lastModified
            } )

            // Loop through each file
            var number = this.data.photos.length
            for (let i = 0; i < filesLength; i++) {

                // If the photo format is accepted
                var ext = files[i].name.split('.').pop().toLowerCase()
                if (acceptedFormats.includes(ext)) {

                    // If HEIC file, ask server for jpg conversion
                    if (ext == 'heic') {
                        loader.setText(files[i].name + 'を*.jpgに変換中...')
                        var jpgblob = await (function () {
                            return new Promise((resolve, reject) => sendFile(files[i], '/api/utils/heic-converter.php', (response) => {
                                fetch(window.location.origin + response.path).then(response => response.blob()).then(blob => resolve(blob))
                            }))
                        } ) ()

                    } else var jpgblob = files[i]

                    // Extract Exif data and start image treatment
                    loader.setText('写真データをアップロード中...')
                    var blobUrl = URL.createObjectURL(jpgblob)
                    let img = new Image()
                    img.src = blobUrl
                    img.addEventListener('load', () => {
                        
                        loader.setText('写真データを解析中...')
                        EXIF.getData(img, async () => {
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
                                var startDatetime = new Date(this.data.checkpoints[0].datetime * 1000)
                                var endDatetime = new Date(this.data.checkpoints[this.data.checkpoints.length - 1].datetime * 1000)
                                if (dateOriginal.getTime() > startDatetime.getTime() && dateOriginal.getTime() < endDatetime.getTime()) {

                                    // Resize, compress photo and generate data url
                                    let blob = await resizeAndCompress(img, 1600, 900, 0.7)
                                    let url = await getDataURLFromBlob(blob)
                                        
                                    // Add photo to map instance
                                    loader.setText('写真を追加中... ' + this.data.photos.length + ' / ' + filesLength)
                                    this.data.photos.push( {
                                        blob,
                                        url,
                                        size: files[i].size,
                                        name: files[i].name,
                                        type: files[i].type,
                                        datetime: dateOriginal.getTime() / 1000,
                                        featured: false,
                                        privacy: 'public',
                                        number
                                    } )
                                    
                                    number++
                                    stopIfFinished(number, filesLength, currentPhotosNumber)

                                } else {
                                    showResponseMessage({error: '\"' + files[i].name + '\"はアクティビティ中に撮影された写真ではありません。'})
                                    filesLength--
                                    stopIfFinished(number, filesLength, currentPhotosNumber)
                                }

                            } else {
                                showResponseMessage({error: '\"' + files[i].name + '\"にはタイムデータが付随されていません。未加工のファイルをアップロードしてください。'})
                                filesLength--
                                stopIfFinished(number, filesLength, currentPhotosNumber)
                            }

                        } )

                    } )
                    
                } else {
                    showResponseMessage({error: '\"' + files[i].name + '\"のファイル形式に対応していません。対応しているファイル形式は次の通り：' + acceptedFormatsString + '.'})
                    filesLength--
                    stopIfFinished(number, filesLength, currentPhotosNumber)
                }
            }

            stopIfFinished(number, filesLength, currentPhotosNumber)

            // Resolve promise after last file has been treated
            function stopIfFinished (number, filesLength, currentPhotosNumber) {
                if (number == filesLength + currentPhotosNumber) {
                    loader.stop()
                    resolve(true)
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
        return new Promise((resolve, reject) => {
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
                this.updatePhotosButtons()
                resolve(true)
            } )
        })
    }

    setFeatured (thisPhoto) {
        if (!thisPhoto.featured) {
            this.data.photos.forEach((photo) => {
                if (photo == thisPhoto) photo.featured = true
                else photo.featured = false
            } )
        } else thisPhoto.featured = false
        this.highlightFeaturedPhoto()
    }

    /**
     * Toggle activity photo privacy setting
     * @param {Object} thisPhoto activity photo data object
     */
    switchPrivacy (thisPhoto) {
        if (thisPhoto.privacy == 'public') thisPhoto.privacy = 'private'
        else if (thisPhoto.privacy == 'private') thisPhoto.privacy = 'limited'
        else if (thisPhoto.privacy == 'limited') thisPhoto.privacy = 'public'
        this.updatePrivacyButton(thisPhoto)
    }

    /**
     * Automatically highlight featured photo
     * @param {Object} photo activity photo data object
     */
    updatePrivacyButton (photo) {
        photo.$thumbnail.querySelector('.pg-ac-privacy-button').innerHTML = this.colorPrivacyString(CFUtils.getPrivacyString(photo.privacy))
    }
    
    /**
     * Set color in accordance with privacy string
     * @param {string} privacyString
     */
    colorPrivacyString (privacyString) {
        switch (privacyString) {
            case '公開': return '<div class="pg-ac-public">' + privacyString + '</>'
            case '非公開': return '<div class="pg-ac-private">' + privacyString + '</>'
            case '限定公開': return '<div class="pg-ac-limited">' + privacyString + '</>'
        }
    }

    // Append photo element before the next checkpoint
    async updatePhotoElement (photo) {

        return new Promise(async (resolve, reject) => {

            var dataUrl
            if (photo.url) dataUrl = photo.url
            else if (photo.blob instanceof Blob) dataUrl = await getDataURLFromBlob(photo.blob)
            else dataUrl = 'data:' + photo.type + ';base64,' + photo.blob

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
            $deleteButton.className = 'pg-ac-photo-button pg-ac-close-button'
            $deleteButton.innerText = 'x'
            $deleteButton.title = '写真を削除する'
            photo.$thumbnail.appendChild($deleteButton)
            // Feature button
            var $featureButton = document.createElement('div')
            $featureButton.className = 'pg-ac-photo-button pg-ac-feature-button'
            $featureButton.innerHTML = '<span class="iconify" data-icon="mdi:feature-highlight"></span>'
            $featureButton.title = 'ハイライト写真に選定する'
            photo.$thumbnail.appendChild($featureButton)
            // Create scenery button
            var $createSceneryButton = document.createElement('div')
            $createSceneryButton.className = 'pg-ac-photo-button pg-ac-createscenery-button'
            $createSceneryButton.innerHTML = '<span class="iconify" data-icon="material-symbols:add-location-alt"></span>'
            $createSceneryButton.title = 'この写真を元に絶景スポットを新規作成する'
            photo.$thumbnail.appendChild($createSceneryButton)
            // Set privacy button
            var $setPrivacyButton = document.createElement('div')
            $setPrivacyButton.className = 'pg-ac-photo-button pg-ac-privacy-button'
            $setPrivacyButton.innerHTML = this.colorPrivacyString(CFUtils.getPrivacyString(photo.privacy))
            $setPrivacyButton.title = 'この写真の公開設定を変更する'
            photo.$thumbnail.appendChild($setPrivacyButton)
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

            // Create scenery listener
            $createSceneryButton.addEventListener('click', () => {

                // Initialize list if necessary
                if (!this.data.sceneriesToCreate) this.data.sceneriesToCreate = []

                // If no similar entry exists yet, create it and highlight thumbnail
                if (!this.data.sceneriesToCreate.includes(photo)) {
                    this.data.sceneriesToCreate.push(photo)
                    photo.$thumbnail.firstChild.classList.add('admin-marker')
                    photo.$thumbnail.querySelector('.pg-ac-createscenery-button').style.color = 'yellow'

                // Else, remove entry in map instance data and set thumbnail back to default 
                } else {
                    for (let i = 0; i < this.data.sceneriesToCreate.length; i++) {
                        if (this.data.sceneriesToCreate[i] == photo) this.data.sceneriesToCreate.splice(i, 1)
                    }
                    photo.$thumbnail.firstChild.classList.remove('admin-marker')
                    photo.$thumbnail.querySelector('.pg-ac-createscenery-button').style.color = 'white'
                }
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
                this.updatePhotosButtons()
                this.displayPhotoMarkers()
            } )

            // Set photo privacy listener
            $setPrivacyButton.addEventListener('click', () => {
                this.switchPrivacy(photo)
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
        this.data.photos.forEach(photo => {
            if (photo.featured) {
                photo.$thumbnail.firstChild.classList.add('selected-marker')
                photo.$thumbnail.querySelector('.pg-ac-feature-button').style.color = "#ff5555"
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

    async checkForCloseSceneries () {
        return new Promise(async (resolve, reject) => {
            // Compare all close sceneries to all uploaded photos location and store similar data
            var photosToAsk = []
            this.data.sceneries.forEach(scenery => {
                this.data.photos.forEach(photo => {
                    var photoLocation = {lng: this.getPhotoLocation(photo)[0], lat: this.getPhotoLocation(photo)[1]}
                    // If photo and scenery have same coords
                    var distance = turf.distance(turf.point([photoLocation.lng, photoLocation.lat]), turf.point([scenery.lngLat.lng, scenery.lngLat.lat]))
                    if (distance < 0.2) {
                        photosToAsk.push({photo, scenery})
                        // If any photo close to an existing scenery have been added to the create sceneries list, discard it
                        if (this.data.sceneriesToCreate) for (let i = 0; i < this.data.sceneriesToCreate.length; i++) {
                            if (this.data.sceneriesToCreate[i].size && this.data.sceneriesToCreate[i].size == photo.size) {
                                this.data.sceneriesToCreate.splice(i, 1)
                                i--
                                showResponseMessage({'error': photo.name + 'の位置が既存の絶景スポット「' + scenery.name + '」と一致しているため、新規の絶景スポットを作成できません。その代わり、写真として「' + scenery.name + '」に追加してください。'})
                            }
                        }
                    }
                } )
            } )
            if (photosToAsk.length > 0) var photosToShare = await this.openSelectPhotosToShareModal(photosToAsk)
            else var photosToShare = []
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
                var $entry = document.createElement('div')
                if (entry.photo.blob instanceof Blob) {
                    var dataUrl = await getDataURLFromBlob(entry.photo.blob)
                    $entry.dataset.photoname = entry.photo.name
                } else {
                    var dataUrl = entry.photo.url
                    $entry.dataset.photoname = entry.photo.filename
                }
                $entry.dataset.sceneryid = entry.scenery.id
                $entry.innerHTML = `
                    <div class="new-ac-window-photo">
                        <img src="` + dataUrl + `" />
                        <div class="new-ac-window-distance">`
                            + (Math.ceil(entry.scenery.distance * 10) / 10) + `km
                        </div>
                    </div>
                    <div class="new-ac-window-scenery-infos">`
                        + entry.scenery.name + `
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
                    if (finalEntry.answer == 'keep') photosToShare.push({photo_name: finalEntry.photo.name, scenery_id: finalEntry.scenery.id})
                } )
                resolve(photosToShare)
            }
        } )
    }

    styleButtons ($clickedButton, $otherButton) {
        if (!$clickedButton.classList.contains('new-ac-btn-kept')) $clickedButton.classList.add('new-ac-btn-kept')
        if ($clickedButton.classList.contains('new-ac-btn-discarded')) $clickedButton.classList.remove('new-ac-btn-discarded')
        if ($otherButton.classList.contains('new-ac-btn-kept')) $otherButton.classList.remove('new-ac-btn-kept')
        if (!$otherButton.classList.contains('new-ac-btn-discarded')) $otherButton.classList.add('new-ac-btn-discarded')
    }

    async clearPhotos () {
        var answer = await openConfirmationPopup('全ての写真が削除されます。宜しいですか？')
        if (answer) {
            this.data.photos.forEach(photo => photo.$thumbnail.remove())
            this.data.photos = []
        }
        this.updatePhotosButtons()
        this.clearPhotoMarkers()
    }

    async changePhotosPrivacy () {
        var answer = await openChoicePopup('全ての写真のプライバシー設定が次のように変更されます。', [
            {value: 'public', text: '公開'},
            {value: 'private', text: '非公開'},
            {value: 'limited', text: '限定公開'}
        ])
        this.data.photos.forEach(photo => {
            photo.privacy = answer
            this.updatePrivacyButton(photo)
        })
    }

    updatePhotosButtons () {
        const clearPhotosButton = document.querySelector('#clearPhotos')
        const changePhotosPrivacyButton = document.querySelector('#changePhotosPrivacy')
        const photosNumberElement = document.querySelector('#photosNumberElement')
        if (this.data.photos.length > 0) {
            clearPhotosButton.classList.remove('hidden')
            changePhotosPrivacyButton.classList.remove('hidden')
            photosNumberElement.innerText = this.data.photos.length + '枚の写真が付随されています。'
        } else {
            if (!clearPhotosButton.classList.contains('hidden')) clearPhotosButton.classList.add('hidden')
            if (!changePhotosPrivacyButton.classList.contains('hidden')) changePhotosPrivacyButton.classList.add('hidden')
            photosNumberElement.innerText = '写真は付随されていません。'
        }
    }

    async createSceneries () {
        return new Promise(async (resolve, reject) => {
            // Store close photos that could also be added
            for (let i = 0; i < this.data.sceneriesToCreate.length; i++) {
                this.data.sceneriesToCreate[i].closePhotos = []
                var currentPhotoLocation = {lng: this.getPhotoLocation(this.data.sceneriesToCreate[i])[0], lat: this.getPhotoLocation(this.data.sceneriesToCreate[i])[1]}
                this.data.photos.forEach(photo => {
                    var photoLocation = {lng: this.getPhotoLocation(photo)[0], lat: this.getPhotoLocation(photo)[1]}
                    if (CFUtils.compareCoords(currentPhotoLocation, photoLocation, 2) && this.data.sceneriesToCreate[i].name != photo.name) this.data.sceneriesToCreate[i].closePhotos.push(photo)
                } )
            }
            // Open modal and get user input data
            var sceneriesToCreate = await this.openCreateSceneriesModal()
            resolve(sceneriesToCreate)
        } )
    }

    async openCreateSceneriesModal () {
        return new Promise ((resolve, reject) => {

            // Focus on route for ensuring that all photos are inside loaded data range
            this.focus(this.routeData)
            
            // Build window structure
            var modal = document.createElement('div')
            modal.classList.add('modal', 'd-block')
            document.querySelector('body').appendChild(modal)
            var confirmationPopup = document.createElement('div')
            confirmationPopup.classList.add('popup', 'fullscreen-popup')
            modal.appendChild(confirmationPopup)
            confirmationPopup.innerHTML = `
            (!) 写真の公開にはルールがあります。<a>こちら</a>で確認してください。`
            var $entriesContainer = document.createElement('div')
            $entriesContainer.className = 'new-ac-entries-container'
            confirmationPopup.appendChild($entriesContainer)

            // Build each scenery element
            this.data.sceneriesToCreate.forEach(async (entry) => {
                var distance = turf.length(turf.lineSlice(this.routeData.geometry.coordinates[0], this.getPhotoLocation(entry), this.routeData))
                var content = ''
                var sceneryElement = document.createElement('div')
                sceneryElement.id = 'form' + entry.number
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
                if (entry.blob instanceof Blob) var dataUrl = await getDataURLFromBlob(entry.blob)
                else var dataUrl = entry.url
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
                // Build scenery form element
                content += `
                    <div class="popup-content">
                        <strong>タイトル :</strong>
                        <input type="text" class="admin-field js-scenery-name"/>
                        <strong>紹介文 :</strong>
                        <textarea class="admin-field js-scenery-description"></textarea>
                    </div>`
                    + $tags + `
                `
                sceneryElement.innerHTML = content
                $entriesContainer.appendChild(sceneryElement)

                // Append listeners to other photos buttons
                if (entry.closePhotos.length > 0) {
                    for (let i = 0; i < entry.closePhotos.length; i++) {
                        var $photo = $entriesContainer.querySelector('#otherPhoto' + entry.closePhotos[i].number)
                        var $yes = $photo.querySelector('.js-yes')
                        var $no = $photo.querySelector('.js-no')
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
                var sceneriesToCreate = []
                var filled = true
                var treatedSceneriesNumber = 0
                this.data.sceneriesToCreate.forEach(async (entry) => {
                    var $sceneryForm = document.querySelector('#form' + entry.number)
                    var name = $sceneryForm.querySelector('.js-scenery-name').value
                    var description = $sceneryForm.querySelector('.js-scenery-description').value
                    var date = entry.datetime
                    var tags = []
                    $sceneryForm.querySelectorAll('.js-segment-tag').forEach($tagInput => {
                        if ($tagInput.checked) tags.push($tagInput.dataset.name)
                    } )
                    // Only attach photo filename if this photo is already registered in blob storage as an activity photo
                    if (entry.filename) {
                        photos = [{
                            filename: entry.filename
                        }]
                    } else {
                        var photos = [{
                            size: entry.size,
                            name: entry.name,
                            type: entry.type
                        }]
                    }
                    entry.closePhotos.forEach(closePhoto => {
                        if (closePhoto.answer && closePhoto.answer == 'keep') {
                            // Only attach photo filename if this photo is already registered in blob storage as an activity photo
                            if (closePhoto.filename) {
                                photos.push(closePhoto.filename)
                            } else {
                                photos.push({
                                    size: closePhoto.size,
                                    name: closePhoto.name,
                                    type: closePhoto.type
                                })
                            }
                        }
                    })

                    var lngLat = {lng: this.getPhotoLocation(entry)[0], lat: this.getPhotoLocation(entry)[1]}
                    var location = await this.getLocation(lngLat)
                    sceneriesToCreate.push( {
                        name,
                        description,
                        tags,
                        date,
                        lngLat,
                        city: location.city,
                        prefecture: location.prefecture,
                        elevation: Math.floor(this.map.queryTerrainElevation(lngLat)),
                        photos
                    } )
                    if (name == '' || description == '') filled = false
                    treatedSceneriesNumber++
                    if (treatedSceneriesNumber == this.data.sceneriesToCreate.length && filled) resolve(sceneriesToCreate)
                    else if (treatedSceneriesNumber == this.data.sceneriesToCreate.length && !filled) showResponseMessage({'error': '絶景スポットにはタイトルと紹介文が必要です。必要に応じて、<a>絶景スポットの共有ルール</a>をご確認ください。'}, {element: document.querySelector('.popup')})
                } )
            } )

            async function buildPhotoElement (photo) {
                return new Promise(async (resolve, reject) => {
                    if (photo.blob instanceof Blob) var dataUrl = await getDataURLFromBlob(photo.blob)
                    else var dataUrl = photo.url
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

    async saveActivity (sceneryPhotos = null, sceneriesToCreate = null) {

        // Start loader
        var loader = new FadeLoader('準備中...')
        loader.start()

        // Add activity data
        var cleanData = {
            activityData: this.activityData
        }
        
        // Remove photos data
        for (var key in this.data) {
            if (key != 'photos') cleanData[key] = this.data[key]
        }
        // Remove marker data
        cleanData.checkpoints.forEach(checkpoint => {
            delete checkpoint.marker
        } )

        // Prepare photo blobs upload
        const photos = this.data.photos
        cleanData.photos = []
        if (photos.length > 0) cleanData.photos = await (async () => {
            return new Promise(async (resolve, reject) => {
                var cleanPhotos = []
                var loadedBlobsNumber = 0
                photos.forEach(async (photo) => {
                    var blob = await blobToBase64(photo.blob)
                    cleanPhotos.push( {
                        blob,
                        size: photo.size,
                        name: photo.name,
                        type: photo.type,
                        lng: this.getPhotoLocation(photo)[0],
                        lat: this.getPhotoLocation(photo)[1],
                        datetime: photo.datetime,
                        featured: photo.featured,
                        privacy: photo.privacy
                    } )
                    loadedBlobsNumber++
                    if (loadedBlobsNumber == photos.length) resolve(cleanPhotos)
                })
            })
        }) ()

        loader.setText('保存中...')

        // If photos need to be added to a scenery, append info data
        if (sceneryPhotos) cleanData.sceneryPhotos = sceneryPhotos
        
        // If sceneries need to be created, append data
        if (sceneriesToCreate) cleanData.sceneriesToCreate = sceneriesToCreate

        console.log(cleanData)
        
        // Send data to server and redirect user
        ajaxSaveActivity(this.apiUrl, cleanData, (activity_id) => {
        
            // Redirect to newly created activity page
            window.location.replace('/activity/' + activity_id)

        }, loader)

        function ajaxSaveActivity (url, jsonData, callback, loader = null) {
            var xhr = getHttpRequest()
            xhr.onreadystatechange = async function () {                            
                // When request have been received
                if (xhr.readyState === 4) {
                    if (loader) loader.stop()
                    callback(JSON.parse(xhr.responseText))
                }
            }
            // Send request through POST method
            xhr.open('POST', url, true)
            xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')
            xhr.setRequestHeader('Content-Type', 'application/json')
            xhr.send(JSON.stringify(jsonData))
        }
    }

}