import GlobalMap from "/map/class/GlobalMap.js"
import Popup from "/map/class/Popup.js"
///import * as util from 'util'

export default class RideMap extends GlobalMap {

    constructor () {
        super()
    }

    apiUrl = '/api/rides/course.php'
    cursor = 0
    data = {
        checkpoints: []
    }
    mode = 'edit'
    edit = false
    session
    options = {
        sf: false
    }

    async updateSession (variable) {
        return new Promise ((resolve, reject) => {
            // Remove marker data from variable checkpoints
            var cleanVariable = JSON.parse(JSON.stringify(variable,
                (key, value) => {
                    if (key === 'marker') return undefined
                    return value
                } )
            ) // Create a deep copy
            console.log(cleanVariable)
            if (variable.data.checkpoints) {
                cleanVariable.data.checkpoints.forEach(checkpoint => {
                    delete checkpoint.marker
                } )
            }
            // If edit property has been set to true, ask API to use 'edit-course' array name rather than default 'course'
            if (this.edit == true) cleanVariable.edit = true
            // Send data to server
            ajaxJsonPostRequest (this.apiUrl, cleanVariable, (response) => {
                if (this.session) this.session.course = response
                console.log(response)
                resolve(response)
            } )
        } )
    }

    async clearSession () {
        return new Promise ((resolve, reject) => {
            var clear = {
                clear: true
            }
            // If edit property has been set to true, ask API to use 'edit-course' array name rather than default 'course'
            if (this.edit == true) clear.edit = true
            // Send data to server
            ajaxJsonPostRequest (this.apiUrl, clear, (response) => {
                if (this.session) this.session.course = response
                resolve(response)
            } )
        } )
    }

    setController () {
        // Controller
        var controller = document.createElement('div')
        controller.className = 'newpickmap-controller'
        this.$map.after(controller)
        // Switch mode 
        var modeDiv = document.createElement('div')
        modeDiv.className = 'newpickmap-controller-label'
        this.$map.after(modeDiv)
        var modeLabel = document.createElement('div')
        modeLabel.innerText = 'Edit mode'
        modeDiv.appendChild(modeLabel)
        var editMouseover = document.createElement('div')
        editMouseover.innerHTML = '<ul><li>Click for adding a marker</li><li>Left click for removing a marker</li><li>Drag for moving a marker</li></ul>'
        editMouseover.id = 'edit'
        editMouseover.classList.add('hidden')
        modeDiv.appendChild(editMouseover)
        var setMouseover = document.createElement('div')
        setMouseover.innerHTML = '<ul><li>Click for setting up marker information</li></ul>'
        setMouseover.id = 'set'
        setMouseover.classList.add('hidden')
        modeDiv.appendChild(setMouseover)
        modeLabel.addEventListener('mouseover', showMouseover)
        modeLabel.addEventListener('mouseout', hideMouseover)
        function showMouseover () {
            document.querySelector('.newpickmap-controller-label').classList.add('mouseover')
            if (this.mode === 'edit') {
                document.querySelector('#edit').classList.remove('hidden')
            } else if (this.mode === 'set') {
                document.querySelector('#set').classList.remove('hidden')
            }
        }
        function hideMouseover () {
            document.querySelector('.newpickmap-controller-label').classList.remove('mouseover')
            if (this.mode === 'edit') {
                document.querySelector('#edit').classList.add('hidden')
            } else if (this.mode === 'set') {
                document.querySelector('#set').classList.add('hidden')
            }
        }
        var controllerSwitchmode = document.createElement('div')
        controllerSwitchmode.className = 'newpickmap-controller-button'
        controllerSwitchmode.innerText = 'Switch mode'
        controllerSwitchmode.setAttribute('title', 'Switch to set marker infos mode')
        controller.appendChild(controllerSwitchmode)
        controllerSwitchmode.addEventListener('click', () => {
            if (this.mode === 'edit') {
                this.mode = 'set'
                modeLabel.innerText = 'Set mode'
                modeLabel.className = 'red-twinkle'
                controllerSwitchmode.setAttribute('title', 'Switch to edit markers mode')
            } else if (this.mode === 'set') {
                this.mode = 'edit'
                modeLabel.innerText = 'Edit mode'
                modeLabel.className = 'blue-twinkle'
                controllerSwitchmode.setAttribute('title', 'Switch to set marker infos mode')
            }
            this.setMode(this.mode)
        } )
        // Clear button
        var controllerClear = document.createElement('div')
        controllerClear.className = 'newpickmap-controller-button'
        controllerClear.innerText = 'Clear'
        controllerClear.setAttribute('title', 'Remove all markers from the map')
        controller.appendChild(controllerClear)
        controllerClear.addEventListener('click', this.clearMarkersHandler)
        // Same start & finish button
        var controllerSFbutton = document.createElement('div')
        controllerSFbutton.className = 'newpickmap-controller-checkbox'
        var controllerSFcheckbox = document.createElement('input')
        controllerSFcheckbox.setAttribute('type', 'checkbox')
        controllerSFbutton.innerHTML = 'Same start & finish'
        controllerSFbutton.setAttribute('title', 'Set start point as start & finish point')
        controllerSFbutton.appendChild(controllerSFcheckbox)
        controller.appendChild(controllerSFbutton)
        controllerSFcheckbox.addEventListener('change', () => {
            if (controllerSFcheckbox.checked){
                this.options.sf = true
            } else {
                this.options.sf = false
            }
            this.updateSession( {
                method: 'pick',
                data: {
                    'options': this.options
                }
            })
            this.setToSF()
        } )
    }

    setMode (mode) {

        if (mode === 'edit') {            
            this.map._markers.forEach( (marker) => {
                marker.getElement().addEventListener('contextmenu', this.removeOnClickHandler)
                marker.setDraggable(true)
                marker.getPopup().options.className = 'hidden' // Hide popup in edit mode
                if (document.querySelector('.marker-popup')) {
                    document.querySelector('.marker-popup').className = 'hidden' // Hide any already opened popup
                }
                marker.getElement().removeEventListener('click', this.setDataHandler)
            } )
            this.map.on('click', this.addMarkerHandler)
            this.map._canvas.style.cursor = 'crosshair'

        } else if (mode === 'set') {
            this.map.off('click', this.addMarkerHandler)
            this.map._canvas.style.cursor = 'grab'
            this.map._markers.forEach( (marker) => {
                marker.getElement().removeEventListener('contextmenu', this.removeOnClickHandler)
                marker.setDraggable(false)
                marker.getPopup().options.className = 'marker-popup'
                marker.getElement().addEventListener('click',  this.setDataHandler)
            } )

        }

    }

    setCheckpointPopupContent (name, description, options = {editable: false, button: false}) {

        var formContent = this.setCheckpointFormContent(name, description, options)

        if (options.button == true) var button = `
            <div class="checkpoint-popup-line">
                <div id="addToCheckpoints" class="mp-button bg-button text-white m-2 mt-0">Add to checkpoints</div>
            </div>`
        else var button = ''
        return `
        <div class="checkpointMarkerForm">`
            + formContent + `
        </div>` 
            + button
    }

    setCheckpointFormContent (name, description, options = {editable: false}) {

        if (options.editable == true) return `
            <div class="checkpoint-popup-line">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <input enctype="multipart/form-data" type="file" name="file" id="file" />
                <label for="file" title="Click to change picture">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--ic" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"><path fill="currentColor" d="M3 4V1h2v3h3v2H5v3H3V6H0V4h3zm3 6V7h3V4h7l1.83 2H21c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2V10h3zm7 9c2.76 0 5-2.24 5-5s-2.24-5-5-5s-5 2.24-5 5s2.24 5 5 5zm-3.2-5c0 1.77 1.43 3.2 3.2 3.2s3.2-1.43 3.2-3.2s-1.43-3.2-3.2-3.2s-3.2 1.43-3.2 3.2z"></path></svg>
                </label>
                <input type="text" id="name" name="name" placeholder="Name" class="admin-field" value="` + name +  `"/>
            </div>
            <div class="checkpoint-popup-line">
                <textarea name="description" placeholder="Description..." id="description" class="admin-field">` + description + `</textarea>
            </div>`
        else {
            if (description.length > 30) description = description.slice(0, 30) + '...' // Shorten description if necessary
            return `
                <div class="checkpoint-popup-line">
                    <div class="bold">` + name +  `</div>
                </div>
                <div class="checkpoint-popup-line">
                    <div>` + description + `</div>
                </div>`
        }
    }

    setDataHandler = (e) => {
        var setData = this.setData.bind(this, e.target.id)
        setData()
    }
    removeOnClickHandler = (e) => {
        var removeOnClick = this.removeOnClick.bind(this, e)
        removeOnClick()
    }
    addMarkerHandler = (e) => {
        var addMarker = this.addMarker.bind(this, e.lngLat)
        addMarker()
    }
    clearMarkersHandler = () => {
        var clearMarkers = this.clearMarkers.bind(this)
        clearMarkers()
    }

    setData (number) {
        var marker = this.data.checkpoints[number].marker
        var popup = marker.getPopup()

        var onInput = (e) => {
            // Build checkpoints variable
            let property = e.target.name
            let value = e.target.value
            this.data.checkpoints[number][property] = value

            // Send post data to API
            this.updateSession( {
                method: this.method,
                data: {
                    checkpoints: this.data.checkpoints
                }
            } )
        }

        var onUpload = (e) => {
            // Extract blob from the file
            let img = e.target.files[0]
            const maxSize = 10000000
            // If image size is less than maxSize Mb
            if (img.size < maxSize) {
                // Read the file into a base64 string
                const reader = new FileReader()
                if (img) {
                    reader.readAsDataURL(img)
                }
                // To be executed after the end of conversion
                reader.addEventListener("load", async () => {
                    this.data.checkpoints[number].img = reader.result
                    this.data.checkpoints[number].img_size = e.target.files[0].size
                    this.data.checkpoints[number].img_name = e.target.files[0].name
                    this.data.checkpoints[number].img_type = e.target.files[0].type
                    // Send checkpoints data to API
                    var response = await this.updateSession( {
                        method: this.method,
                        data: {
                            checkpoints: this.data.checkpoints
                        }
                    })
                    // In case of error
                    var $popup = popup.getElement()
                    if ('error' in response) {
                        console.log('errorincheckpoints')
                        displayError($popup, response.error)
                    // If no error, display thumbnail
                    } else {
                        // Remove previous error block if there is one
                        if ($popup.querySelector('.error-block')) {
                            $popup.querySelector('.error-block').remove()
                        }
                        this.displayThumbnail(marker)
                    }
                }, false)
            // If size image exceeds max size
            } else {
                console.log('errorsize')
                var errorMessage = 'This file exceeds size limit (' + Math.round(maxSize / 1000000) + 'Mb)'
                displayError(popup, errorMessage)
            }

            function displayError (popup, error) {
                var $popup = popup.getElement()
                // Remove previous image if there is one
                if ($popup.querySelector('.checkpoint-popup-img-container')) {
                    $popup.querySelector('.checkpoint-popup-img-container').remove()
                }
                // Remove previous error block if there is one
                if ($popup.querySelector('.error-block')) {
                    $popup.querySelector('.error-block').remove()
                }
                // Display error message sent back by API
                let $errorBlock = document.createElement('div')
                $errorBlock.innerHTML = '<p class="error-message">' + error + '</p>'
                $errorBlock.classList.add('checkpoint-popup-img-error', 'error-block', 'm-0')
                $popup.querySelector('.checkpointMarkerForm').before($errorBlock)
                // Remove error message as soon as popup is closed 
                popup.once('close', () => $errorBlock.remove() )
            }
        }

        // Treat data
        var form = popup._content.querySelector('.checkpointMarkerForm')
        form.querySelector('#name').addEventListener('change', onInput)
        form.querySelector('#description').addEventListener('change', onInput)
        form.querySelector('#file').addEventListener('change', onUpload)
    }

    displayThumbnail (marker) {
        // Get checkpoint number
        if (marker.getElement().innerText == 'F') var number = this.data.checkpoints.length - 1
        else if (marker.getElement().innerText == 'S' || marker.getElement().innerText == 'SF' ) var number = 0
        else var number = parseInt(marker.getElement().innerText)
        // Get file data into file variable
        if (this.data.checkpoints[number].img) {
            // If image data is stored as a blob (object coming from database)
            if (typeof this.data.checkpoints[number].img === 'object' && this.data.checkpoints[number].img !== null) {
                if (this.data.checkpoints[number].img.blob) var img = 'data:' + this.data.checkpoints[number].img.type + ';base64,' + this.data.checkpoints[number].img.blob
            // Else
            } else {
                if (!this.data.checkpoints[number].img_type || this.data.checkpoints[number].img.includes('data:' + this.data.checkpoints[number].img_type + ';base64,')) { // Add URL data type prefix if necessary
                    var img = this.data.checkpoints[number].img
                } else {
                    var img = 'data:' + this.data.checkpoints[number].img_type + ';base64,' + this.data.checkpoints[number].img
                }
            }
        }

        // Set or update thumbnail
        var $popup = marker.getPopup()._content
        if (img && !$popup.querySelector('.checkpoint-popup-img-container')) { // If there is a file registered in checkpoints data but it doesn't exist in this popup element yet
            var photoInput = document.createElement('div')
            photoInput.className = 'checkpoint-popup-img-container'
            photoInput.innerHTML = '<img class="checkpoint-popup-img" />'
            $popup.querySelector('.checkpointMarkerForm').before(photoInput)
            $popup.querySelector('.checkpoint-popup-img').src = img
        } else if (img && $popup.querySelector('.checkpoint-popup-img-container')) { // If there is a file registered in checkpoints data and a thumbnail is already displayed in this popup element
            $popup.querySelector('.checkpoint-popup-img').src = img
        }
    }

    clearMarkers () {
        var length = this.map._markers.length
        for (let i = 0; i < length; i++) {
            this.map._markers[0].remove()
        }
        this.cursor = 0
        this.data.checkpoints = []
        this.updateSession( {
            method: this.method,
            data: this.data.checkpoints
        } )
    }

    // Update checkpoints inner HTML if same start & finish option is on
    setToSF ($onAdd = false) {
        var markers = this.map._markers
        if (this.options.sf === true) {
            markers.forEach( (marker) => {
                var $marker = marker.getElement()
                if ($onAdd) {
                    if ($marker.innerText == 'S') {
                        $marker.innerText = 'SF'
                        $marker.classList.add('checkpoint-marker-startfinish')
                    } else if ($marker.innerText == 'F') {
                        $marker.innerText = this.cursor
                        $marker.classList.remove('checkpoint-marker-goal')
                    }
                } else {
                    if ($marker.innerText == 'S') {
                        $marker.innerText = 'SF'
                        $marker.classList.add('checkpoint-marker-startfinish')
                    } else if ($marker.innerText == 'F') {
                        $marker.innerText = this.cursor - 1
                        $marker.classList.remove('checkpoint-marker-goal')
                    }
                }
            } )
        } else if (this.options.sf === false) {
            markers.forEach( (marker) => {
                var $marker = marker.getElement()
                if ($onAdd) {
                    if ($marker.innerText == 'SF') {
                        $marker.innerText = 'S'
                        $marker.classList.remove('checkpoint-marker-startfinish')
                        $marker.classList.add('checkpoint-marker-start')
                    }
                    if ($marker.innerText == this.cursor) {
                        $marker.innerText = 'F'
                        $marker.classList.add('checkpoint-marker-goal')
                    }
                } else {
                    if ($marker.innerText == 'SF') {
                        $marker.innerText = 'S'
                        $marker.classList.remove('checkpoint-marker-startfinish')
                        $marker.classList.add('checkpoint-marker-start')
                    }
                    if ($marker.innerText == this.cursor - 1) {
                        $marker.innerText = 'F'
                        $marker.classList.add('checkpoint-marker-goal')
                    }
                }
            } )
        }
    }

    displayCheckpoints () {
        for (let j = 0; j < this.data.checkpoints.length; j++) {    
            // Create and add marker
            var element = this.createCheckpointElement(j) 
            var marker = new mapboxgl.Marker(
                {
                    draggable: false,
                    scale: 0.8,
                    element: element
                }
            )
            marker.setLngLat(this.data.checkpoints[j].lngLat)
            marker.addTo(this.map)

            // Generate popup
            this.generateMarkerPopup(marker, j, this.data.checkpoints[j].name, this.data.checkpoints[j].description, this.data.checkpoints[j].img)

            // Set cursor pointer on mouse hover
            marker.getElement().style.cursor = 'pointer'
            // Attach remove on left click handler except from start and goal marker
            if ((this.options.sf == false && j != 0 && j != this.data.checkpoints.length - 1) || (this.options.sf == true && j != 0)) {
                marker.getElement().addEventListener('contextmenu', this.removeOnClickHandler)
            }

            // Append marker element to checkpoint
            this.data.checkpoints[j].marker = marker

            this.cursor++
        }
    }

    generateMarkerPopup (marker, number, name = false, description = '', img = false) {
        // Generate popup
        // Define name
        if (!name) {
            if (number == 0) name = 'Start'
            else if (this.options.sf == false && number == this.data.checkpoints.length - 1) name = 'Goal'
            else name = ''
        }
        var content = this.setCheckpointPopupContent(name, description, {editable: true})
        let popup = new Popup({closeButton: false, maxWidth: '180px'}, {markerHeight: 24}).popup
        popup.setHTML(content)
        marker.setPopup(popup)
        this.displayThumbnail(marker)
        // Set data
        popup.on('open', () => {
            let innerText = marker.getElement().innerText
            if (innerText == 'S' || innerText == 'SF') this.setData(0)
            else if (innerText == 'F') this.setData(this.data.checkpoints.length - 1)
            else this.setData(parseInt(innerText))
        } )
        return popup
    }
}