import RideMap from "/map/class/ride/RideMap.js"
import Popup from "/map/class/Popup.js"

export default class RidePickMap extends RideMap {

    constructor () {
        super()
    }

    method = 'pick'

    addMarker (lngLat) {
        var element = this.createCheckpointElement(this.cursor) 
        var marker = new mapboxgl.Marker(
            {
                draggable: true,
                scale: 0.8,
                element: element
            }
        )

        marker.setLngLat(lngLat)

        // Update and upload checkpoints data to API
        this.data.checkpoints[this.cursor] = {
            lngLat: marker.getLngLat(),
            elevation: Math.floor(this.map.queryTerrainElevation(marker.getLngLat())),
            number: this.cursor,
            marker
        }
        this.updateSession( {
            method: this.method,
            data: {
                'checkpoints': this.data.checkpoints
            }
        })

        // Ask marker to update checkpoints data after each dragging
        marker.on('dragend', (e) => {
            this.data.checkpoints[e.target._element.id].lngLat = e.target.getLngLat()
            this.data.checkpoints[e.target._element.id].elevation = Math.floor(this.map.queryTerrainElevation(e.target.getLngLat()))
            this.updateSession( {
                method: this.method,
                data: {
                    'checkpoints': this.data.checkpoints
                }
            })
        } )
        var name = this.data.checkpoints[this.cursor].name, description = this.data.checkpoints[this.cursor].description
        if (!name) { name = '' }
        if (!description) { description = '' }
        var content = this.setCheckpointPopupContent(name, description, {editable: true})
        let popup = new Popup({closeButton: false, maxWidth: '180px'}, {markerHeight: 24}).popup
        popup.setHTML(content)
        marker.setPopup(popup)
        popup.options.className = 'hidden' // Hide popup as creating in edit mode
        marker.addTo(this.map)

        // Update existing markers
        this.updateMarkers({exceptSF: false})

        // Update markers inner HTML if same start & finish option is on
        this.setToSF(true)
        
        this.cursor++
        console.log(this.cursor)
        console.log(this.data.checkpoints)
        
        // Removing a marker and updating existing markers
        marker.getElement().addEventListener('contextmenu', (e) => this.removeOnClick(e))

        // Set bounds according to existing markers
        if (this.cursor === 1) { // If added marker is the first one, fly to it
            this.map.flyTo( {
                center: marker._lngLat,
                zoom: 12,
                speed: 1,
                curve: 1
            } )
        } else this.defineBounds(marker) // Else, redefine bounds
        
    }

    setCheckpointPopupContent (name, description, options = {editable: false}) {

        if (options.editable == true) return `
        <div class="checkpointMarkerForm">
            <div class="checkpoint-popup-line">
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <input enctype="multipart/form-data" type="file" name="file" id="file" />
                <label for="file" title="写真を変更する">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" aria-hidden="true" role="img" class="iconify iconify--ic" width="20" height="20" preserveAspectRatio="xMidYMid meet" viewBox="0 0 24 24" data-icon="ic:baseline-add-a-photo" data-width="20" data-height="20"><path fill="currentColor" d="M3 4V1h2v3h3v2H5v3H3V6H0V4h3zm3 6V7h3V4h7l1.83 2H21c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2V10h3zm7 9c2.76 0 5-2.24 5-5s-2.24-5-5-5s-5 2.24-5 5s2.24 5 5 5zm-3.2-5c0 1.77 1.43 3.2 3.2 3.2s3.2-1.43 3.2-3.2s-1.43-3.2-3.2-3.2s-3.2 1.43-3.2 3.2z"></path></svg>
                </label>
                <input type="text" id="name" name="name" placeholder="タイトル" class="admin-field" value="` + name +  `"/>
            </div>
            <textarea name="description" placeholder="詳細..." id="description" class="admin-field">` + description + `</textarea>
        </div>`

        else return `
        <div class="checkpointMarkerForm">
            <div class="checkpoint-popup-line">
                <div class="bold">` + name +  `</div>
            </div>
            <div>` + description + `</div>
        </div>`
    }

    displayCheckpoints () {
        for (let j = 0; j < this.data.checkpoints.length; j++) {    
            var element = this.createCheckpointElement(j)
            let marker = new mapboxgl.Marker(
                {
                    draggable: true,
                    scale: 0.8,
                    element: element
                }
            )
            marker.setLngLat(this.data.checkpoints[j].lngLat)

            // Ask marker to update checkpoints data after each dragging
            marker.on('dragend', (e) => {
                this.data.checkpoints[e.target._element.id].lngLat = e.target.getLngLat()
                this.data.checkpoints[e.target._element.id].elevation = Math.floor(this.map.queryTerrainElevation(e.target.getLngLat()))                            
                this.updateSession( {
                    method: this.method,
                    data: {
                        'checkpoints': this.data.checkpoints
                    }
                })
            } )

            // Removing a marker and updating existing markers
            marker.getElement().addEventListener('contextmenu', (e) => this.removeOnClick(e))

            // Set and add popup
            var popup = this.generateMarkerPopup(marker, j, this.data.checkpoints[j].name, this.data.checkpoints[j].description, this.data.checkpoints[j].img)
            popup.options.className = 'hidden' // Hide popup as creating in edit mode

            // Create marker
            marker.addTo(this.map)
            
            // Update existing markers
            this.updateMarkers({exceptSF: false})

            // Set bounds according to loaded marker
            this.defineBounds(marker)

            this.cursor++
            console.log(this.cursor)
            console.log(this.data.checkpoints)
        }
    }

    createCheckpointElement (i) {
        var element = document.createElement('div')
        element.className = 'checkpoint-marker'
        element.id = i
        // If this is the first marker, set it to 'S' or 'SF'
        if (i === 0 && this.options.sf == false) {
            element.innerHTML = 'S'
            element.className = 'checkpoint-marker checkpoint-marker-start'
        } else if (i === 0 && this.options.sf == true) {
            element.innerHTML = 'SF'
            element.className = 'checkpoint-marker checkpoint-marker-startfinish'
            // If this is the last marker, set it to 'F'
        } else if (this.options.sf == false && i == this.data.checkpoints.length) {
            element.innerHTML = 'F'
            element.className = 'checkpoint-marker checkpoint-marker-goal'
        // Else, set it to i
        } else element.innerHTML = i
        return element
    }

    defineBounds (marker = this.map._markers[this.cursor-2]) { // Define bounds according to existing markers latLng
        var markerslist = this.map._markers
        var maxlng = marker._lngLat.lng + 0.02
        var minlng = marker._lngLat.lng - 0.02
        var maxlat = marker._lngLat.lat + 0.02
        var minlat = marker._lngLat.lat - 0.02
        for (let i = 0; i < markerslist.length; i++) {
            if (markerslist[i]._lngLat.lng > maxlng) {
                maxlng = markerslist[i]._lngLat.lng + 0.01
            }
            if (markerslist[i]._lngLat.lng < minlng) {
                minlng = markerslist[i]._lngLat.lng - 0.01
            }
            if (markerslist[i]._lngLat.lat > maxlat) {
                maxlat = markerslist[i]._lngLat.lat + 0.01
            }
            if (markerslist[i]._lngLat.lat < minlat) {
                minlat = markerslist[i]._lngLat.lat - 0.01
            }
        }
        var bounds = [[minlng, minlat], [maxlng, maxlat]]
        this.map.fitBounds(bounds)
    }

    removeOnClick (e) {
        const id = e.target.id
        const checkpoints = this.data.checkpoints

        // If removes goal when more than 2 markers on the map
        if (e.target.innerHTML === 'F' && this.cursor > 2) {
            checkpoints[this.cursor - 2].marker.getElement().innerHTML = 'F'
            checkpoints[this.cursor - 2].marker.getElement().classList.add('checkpoint-marker-goal')
        }
        // If removes start when it is the only marker on the map
        if (e.target.innerHTML === 'S' && this.cursor == 2) {
            checkpoints[1].marker.getElement().innerHTML = 'S'
            checkpoints[1].marker.getElement().classList.remove('checkpoint-marker-goal')
            checkpoints[1].marker.getElement().classList.add('checkpoint-marker-start')
        }

        // Update all existing markers according to the deleted marker
        for (let j = id; j < this.cursor; j++) {
            checkpoints[j].marker.getElement().innerHTML = j - 1
            checkpoints[j].marker.getElement().id = j - 1
            if (j > id) checkpoints[j].number-- // Decrement checkpoint numbers above removed one
            if (j === 1) {
                checkpoints[1].marker.getElement().innerHTML = 'S'
                checkpoints[1].marker.getElement().classList.add('checkpoint-marker-start')
            }
        }

        // If there is more than one marker on the map
        if (this.cursor > 2) checkpoints[this.cursor - 1].marker.getElement().innerHTML = 'F'

        // Remove marker
        checkpoints[id].marker.remove()

        // Update and upload checkpoints data to API
        this.data.checkpoints.splice(id, 1)
        this.updateSession( {
            method: this.method,
            data: {
                checkpoints
            }
        })

        this.cursor--
        console.log(this.cursor)
        console.log(this.data.checkpoints)
    }

    // Update meeting place and finish place information (only if not set or having changed)
    async updateMeetingFinishPlace () {
        return new Promise (async (resolve, reject) => {
            // Check whether meeting place has already been set
            if (this.session.course) var course = this.session.course
            else if (this.session['edit-course']) var course = this.session['edit-course']
            // If meeting place has already been set
            if (course.meetingplace && course.meetingplace.length !== 0) {
                // If meeting place (or S/F place) has been changed
                if (course.meetingplace.lngLat.lng != this.data.checkpoints[0].lngLat.lng || (this.options.sf === true && course.finishplace.lngLat.lng != this.data.checkpoints[0].lngLat.lng)) { // If meeting place have changed
                    var meetingplacelngLat = this.data.checkpoints[0].lngLat
                    var meetingplacegeolocation = await this.getCourseGeolocation(meetingplacelngLat)
                    var meetingplace = {'geolocation': meetingplacegeolocation, 'lngLat': meetingplacelngLat}
                    if (this.options.sf === true) {
                        var finishplace = meetingplace
                        var geolocationdata = {'meetingplace': meetingplace, 'finishplace': finishplace}
                        this.updateSession( {
                            method: this.method,
                            data: geolocationdata
                        } )
                    } else {
                        var geolocationdata = {'meetingplace': meetingplace}
                        this.updateSession( {
                            method: this.method,
                            data: geolocationdata
                        } )
                    }
                    console.log('Meeting point data (or finish point data if SF option on) does not match previously registered coords. Updating meeting point coords')
                }
                // If finish place has been changed
                if (this.options.sf === false) {
                    if (course.finishplace.lngLat.lng != this.data.checkpoints[this.cursor-1].lngLat.lng) {
                        var finishplacegeolocation = await this.getCourseGeolocation(this.data.checkpoints[this.cursor-1].lngLat)
                        var finishplacelngLat = this.data.checkpoints[this.cursor-1].lngLat
                        var finishplace = {'geolocation': finishplacegeolocation, 'lngLat': finishplacelngLat}
                        var geolocationdata = {'finishplace': finishplace}
                        console.log('Finish point data does not match the last marker coords. Updating finish point coords')
                        this.updateSession( {
                            method: this.method,
                            data: geolocationdata
                        } )
                    }
                }
            // If meeting place is undefined
            } else {
                console.log('No geolocation data have been set yet. Requesting map server for geolocation data')
                var meetingplacelngLat = this.data.checkpoints[0].lngLat
                var meetingplacegeolocation = await this.getCourseGeolocation(meetingplacelngLat)
                var meetingplace = {'geolocation': meetingplacegeolocation, 'lngLat': meetingplacelngLat}
                if (this.options.sf === false) {
                    var finishplacegeolocation = await this.getCourseGeolocation(this.data.checkpoints[this.cursor-1].lngLat)
                    var finishplacelngLat = this.data.checkpoints[this.cursor-1].lngLat
                    var finishplace = {'geolocation': finishplacegeolocation, 'lngLat': finishplacelngLat}
                } else if (this.options.sf === true) {
                    var finishplace = meetingplace
                }
                var geolocationdata = {'meetingplace': meetingplace, 'finishplace': finishplace}
                console.log(geolocationdata)
                this.updateSession( {
                    method: this.method,
                    data: geolocationdata
                } )
            }
            resolve()
        } )
    }

}