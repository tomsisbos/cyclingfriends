import CFUtils from "/class/utils/CFUtils.js"
import RideMap from "/class/maps/ride/RideMap.js"
import Popup from "/class/maps/Popup.js"
import FadeLoader from "/class/loaders/FadeLoader.js"
import CircleLoader from "/class/loaders/CircleLoader.js"

export default class RideDrawMap extends RideMap {

    constructor () {
        super()
    }

    route
    routeSource
    method = 'draw'

    /**
     * Load a route inside the map
     * @param {Integer} routeId Id of route to load
     * @param {String} options.loader if displaying a loader on the map during loading, text to display inside
     * @returns {Promise}
     */
    async loadRoute (routeId, options = {}) {
        
        return new Promise ( async (resolve, reject) => {
            if (options.loader) {
                var loader = new FadeLoader(options.loader, this.$map)
                loader.start()
            }
            ajaxGetRequest ('/api/route.php' + "?route-load=" + routeId, async (route) => {
                if (options.loader) loader.stop()
                this.route = route
                // Update labels and values
                var distanceDiv = document.querySelector('#distanceDiv')
                distanceDiv.innerText = Math.ceil(route.distance * 10) / 10 + 'km'
                ajaxGetRequest ('/api/ride.php' + "?get-terrain-value=" + route.id, async (response) => {
                    var terrainDiv = document.querySelector('#terrainDiv')
                    terrainDiv.innerText = response
                } )
                // If no course description data stored in database, load route description as course description by default
                if (this.edit) {
                    if (this.session && (!this.session['edit-forms'] || (this.session['edit-forms'] && !this.session['edit-forms'][2]['course-description']))) document.querySelector('#courseDescriptionTextarea').value = route.description
                } else {
                    if (this.session && (!this.session['forms'] || (this.session['forms'] && !this.session['forms'][2]['course-description'] && route.description != ''))) document.querySelector('#courseDescriptionTextarea').value = route.description
                }
                // Select current route by default
                var $selectRoute = document.querySelector('#selectRoute')
                for (const option of $selectRoute.options) {
                    if (option.value == this.route.id) option.selected = 'selected'
                }
                // Get coords sorted
                var coords = []
                route.coordinates.forEach( (lngLat) => {
                    coords.push([lngLat.lng, lngLat.lat])
                } )
                // Add route layer
                if (this.map.getSource('route')) this.clearRoute()
                this.addRouteLayer( {
                    type: 'Feature',
                    properties: {
                        tunnels: route.tunnels
                    },
                    geometry: {
                        type: 'LineString',
                        coordinates: coords
                    }
                } )
                this.map.on('mouseenter', 'route', () => this.map.getCanvas().style.cursor = 'crosshair')
                this.map.on('mouseleave', 'route', () => this.map.getCanvas().style.cursor = 'grab')
                this.paintTunnels(route.tunnels)
                this.updateDistanceMarkers()
                this.focus(this.map.getSource('route')._data)
                document.querySelector('#profileBox').style.display = 'block'
                document.querySelector('#js-draw .rd-course-fields').style.paddingTop = 'calc(420px + 15vh)'
                this.profile.generate()
                this.addStartGoalMarkers()
                let profileLoader = new CircleLoader(document.getElementById('profileBox'), {absolute: true})
                profileLoader.start()
                await this.displayCloseSceneries(0.5)
                    .then(async () => {
                        profileLoader.stop()
                        this.profile.clearData()
                        this.profile.generate({
                            poiData: {
                                rideCheckpoints: this.data.checkpoints,
                                sceneries: this.mapdata.sceneries
                            }
                        })
                    })
                    .then(() => resolve(true))
            } )
        } )
    }

    async displayCloseSceneries (range) {
        
        return new Promise ( async (resolve, reject) => {

            // Display close sceneries inside the map
            ajaxGetRequest ('/api/map.php' + "?display-sceneries=" + this.route_id + '&details=true', async (response) => {

                this.mapdata.sceneries = await this.getClosestSceneries(response, range)

                // Display on map
                this.addSceneries(this.mapdata.sceneries)
                
                // Display thumbnails
                // Get sceneries on route number
                this.mapdata.sceneries.forEach( (scenery) => {
                    if (scenery.on_route) this.sceneriesOnRouteNumber++
                } )

                // Get most relevant image url for each scenery and add it to map instance data
                var closestPhotos = await this.getClosestPhotos(this.mapdata.sceneries)
                closestPhotos.forEach(photo => {
                    this.mapdata.sceneries.forEach(scenery => {
                        if (scenery.id == photo.id) scenery.url = photo.data.url
                    } )
                } )

                resolve(this.mapdata.sceneries)
            } )
        } )
    }

    addSceneries (sceneries) {
        sceneries.forEach( (scenery) => {
            if (scenery.on_route) {
                var content = this.setCheckpointPopupContent(scenery.name, scenery.description, {button: true})
                var marker = this.addSceneryMarker(scenery, content)
                // Save element and its properties for switching to checkpoint
                var marker_id = marker.getElement().id
                var formerElement = marker.getElement().cloneNode(true)
                var formerElementHTML = formerElement.innerHTML
                var popup = marker.getPopup()
                // Update profile when a scenery is selected
                popup.on('open', () => {
                    // If scenery photo is not displayed yet
                    if (!popup.getElement().querySelector('img')) {
                        // Display scenery photo
                        var photoInput = document.createElement('div')
                        photoInput.className = 'checkpoint-popup-img-container'
                        photoInput.innerHTML = '<img class="checkpoint-popup-img" />'
                        popup.getElement().querySelector('.checkpointMarkerForm').before(photoInput)
                        popup.getElement().querySelector('.checkpoint-popup-img').src = scenery.url
                        this.profile.generate({
                            poiData: {
                                rideCheckpoints: this.data.checkpoints,
                                sceneries: this.mapdata.sceneries
                            }
                        })
                        // Add "addToCheckpoints" button click event handler
                        var $button = popup._content.querySelector('#addToCheckpoints')
                        $button.addEventListener('click', async (e) => {
                            var $button = e.target
                            // If this scenery have not been added to checkpoints yet, add it
                            if ($button.innerText == 'チェックポイントに追加') {
                                // Change button text
                                $button.innerText = 'チェックポイントから除外'
                                // Allow checkpoint content edition
                                var formContent = this.setCheckpointFormContent(scenery.name, scenery.description, {editable: true, button: true})
                                popup.getElement().querySelector('.checkpointMarkerForm').innerHTML = formContent
                                // Update and upload checkpoints basic data
                                // Insert new checkpoint
                                this.data.checkpoints.push( {
                                    lngLat: marker.getLngLat(),
                                    elevation: Math.floor(this.map.queryTerrainElevation(marker.getLngLat())),
                                    name: popup.getElement().querySelector('#name').value,
                                    description: popup.getElement().querySelector('#description').innerHTML,
                                    url: popup.getElement().querySelector('img').src,
                                    marker
                                } )
                                // Sort checkpoints
                                var current = {lng: scenery.lng, lat: scenery.lat}
                                await this.sortCheckpoints()
                                var number = this.getCheckpointNumber(this.data.checkpoints, current)
                                this.updateMarkers()
                                this.updateSession( {
                                    method: this.method,
                                    data: {
                                        'checkpoints': this.data.checkpoints
                                    }
                                } )
                                // Style checkpoint element created after markers updating
                                marker.getElement().className = 'checkpoint-marker mapboxgl-marker mapboxgl-marker-anchor-center'
                                marker.getElement().id = number
                                // Set data
                                this.setData(number)
                                this.cursor++
                            // If this scenery have been added to checkpoints, remove it
                            } else {
                                var number = parseInt(marker.getElement().innerText)
                                // Change button text
                                $button.innerText = 'チェックポイントに追加'
                                // Disable checkpoint content edition
                                var formContent = this.setCheckpointFormContent(scenery.name, scenery.description, {editable: false, button: true})
                                popup.getElement().querySelector('.checkpointMarkerForm').innerHTML = formContent
                                // Update markers
                                this.updateMarkers()
                                // Remove checkpoint element and display former one back
                                marker.getElement().className = 'scenery-marker'
                                marker.getElement().id = marker_id
                                marker.getElement().innerHTML = formerElementHTML
                                // Remove this checkpoint data
                                this.data.checkpoints.splice(number, 1)
                                await this.sortCheckpoints()
                                this.updateSession( {
                                    method: this.method,
                                    data: {
                                        'checkpoints': this.data.checkpoints
                                    }
                                })
                                this.cursor--
                            }
                            this.profile.generate({
                                poiData: {
                                    rideCheckpoints: this.data.checkpoints,
                                    sceneries: this.mapdata.sceneries
                                }
                            })
                        } )
                    }
                    return marker 
                } )
            }
        } )
    }

    addSceneryMarker (scenery, content) {
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + scenery.thumbnail
        icon.classList.add('scenery-icon')
        if (scenery.on_route === true) icon.classList.add('oncourse-marker')
        element.appendChild(icon)
        this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )

        var content = this.setCheckpointPopupContent(scenery.name, scenery.description, {editable: false, button: true})
        let popupInstance = new Popup({closeButton: false, maxWidth: '180px'}, {markerHeight: 24})
        let popup = popupInstance.popup
        popup.setHTML(content)
        marker.setPopup(popup)
        marker.setLngLat([scenery.lng, scenery.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'scenery' + scenery.id
        marker.getElement().className = 'scenery-marker'
        marker.getElement().dataset.id = scenery.id
        marker.getElement().dataset.user_id = scenery.user_id
        popup.on('open', (e) => {
            // Add 'selected-marker' class to selected marker
            document.getElementById('scenery' + scenery.id).firstChild.classList.add('selected-marker')
        } )
        popup.on('close', (e) => {
            // Remove 'selected-marker' class from selected marker if there is one
            if (document.getElementById('scenery' + scenery.id)) {
                if (document.getElementById('scenery' + scenery.id).firstChild.classList) { // Ensure first child is an element
                    document.getElementById('scenery' + scenery.id).firstChild.classList.remove('selected-marker')
                }
            }
        } )
        return marker        
    }

    addMarker (lngLat) {
        var number = this.setCheckpointNumber(this.data.checkpoints, lngLat)
        var element = this.createCheckpointElement(number)
        let marker = new mapboxgl.Marker(
            {
                draggable: false,
                scale: 0.8,
                element: element
            }
        )
        marker.setLngLat(lngLat)
        marker.addTo(this.map)

        // Update and upload checkpoints data to API
        this.data.checkpoints[this.cursor] = {
            lngLat: marker.getLngLat(),
            elevation: Math.floor(this.map.queryTerrainElevation(marker.getLngLat())),
            marker
        }
        this.updateSession( {
            method: this.method,
            data: this.data
        })

        // Generate popup
        this.generateMarkerPopup(marker, number)

        // Set cursor pointer on mouse hover
        marker.getElement().style.cursor = 'pointer'
        // Add remove listener on click (except for start and goal markers)
        if (lngLat != this.route.coordinates[0] && lngLat != this.route.coordinates[this.route.coordinates.length - 1]) {
            marker.getElement().addEventListener('contextmenu', (e) => this.removeOnClick(e))
        }
        
        this.cursor++

        return marker
    }

    getCheckpointNumber (checkpoints, current) {
        var number
        checkpoints.forEach( (checkpoint) => {
            if (checkpoint.lngLat.lng == current.lng) {
                number = parseInt(checkpoint.number)
            }
        } )
        return number
    }

    async addStartGoalMarkers () {
        // If no start has been detected, add it
        if (!this.data.checkpoints || !this.data.checkpoints[0]) {
            // Add checkpoints
            var routeCoords = this.route.coordinates
            var lineString = turf.lineString([[routeCoords[0].lng, routeCoords[0].lat], [routeCoords[routeCoords.length - 1].lng, routeCoords[routeCoords.length - 1].lat]])
            // If distance from start to goal is less than 200m, set them to the same point
            if (turf.length(lineString) < 0.2) {
                var startMarker = this.addMarker(this.route.coordinates[0])
                var goalMarker = startMarker
                this.options.sf = true
                this.setToSF()
                var options = {
                    sf: true
                }
            } else {
                var startMarker = this.addMarker(routeCoords[0])
                var goalMarker = this.addMarker(routeCoords[routeCoords.length - 1])
                this.options.sf = false
                this.setToSF()
                var options = {
                    sf: false
                }
            }
            // Update course infos
            await this.sortCheckpoints()
            this.updateThumbnails()
        // Else, simply set course data
        } else {
            var options = this.options
        }
        // Update course data
        var data = {
            meetingplace: {
                geolocation: CFUtils.buildGeolocationFromString(this.route.startplace)
            },
            finishplace: {
                geolocation: CFUtils.buildGeolocationFromString(this.route.goalplace)
            },
            checkpoints: this.data.checkpoints,
            options
        }
        this.updateSession( {
            method: this.method,
            data
        } )
    }

    createCheckpointElement (i) {
        var element = document.createElement('div')
        element.className = 'checkpoint-marker'
        element.id = i
        if (i === 0 && this.options.sf == false) { // If this is the first marker, set it to 'S'
            element.innerHTML = 'S'
            element.className = 'checkpoint-marker checkpoint-marker-start'
        } else if (i === 0 && this.options.sf == true) {
            element.innerHTML = 'SF'
            element.className = 'checkpoint-marker checkpoint-marker-startfinish'
        } else if (this.options.sf == false && i == this.data.checkpoints.length) { // If this is the last marker, set it to 'F'
            element.innerHTML = 'F'
            element.className = 'checkpoint-marker checkpoint-marker-goal'
        } else { // Else, set it to i
            element.innerHTML = i
        }
        return element
    }

    async removeOnClick (e) {
        var number = parseInt(e.target.innerHTML)
        this.data.checkpoints[number].marker.remove()
        this.data.checkpoints.splice(number, 1)
        await this.sortCheckpoints()
        this.updateMarkers()

        // Update and upload checkpoints data to API
        this.updateSession( {
            method: this.method,
            data: {
                checkpoints: this.data.checkpoints
            }
        })

        this.cursor--

        this.profile.generate({
            poiData: {
                rideCheckpoints: this.data.checkpoints,
                sceneries: this.mapdata.sceneries
            }
        })
    }

    getCheckpointNumber (checkpoints, current) {
        var number
        checkpoints.forEach( (checkpoint) => {
            if (checkpoint.lngLat.lng == current.lng) {
                number = parseInt(checkpoint.number)
            }
        } )
        return number
    }

    async addMarkerOnRoute (lngLat) {
        // Generate marker
        this.addMarker(lngLat)
        // Update checkpoints data
        await this.sortCheckpoints()
        this.updateMarkers()
        this.updateSession( {
            method: this.method,
            data: {
                checkpoints: this.data.checkpoints
            }
        } )
        this.profile.generate({
            poiData: {
                rideCheckpoints: this.data.checkpoints,
                sceneries: this.mapdata.sceneries
            }
        })
    }

    async validateCourse (e) {
        e.preventDefault()

        // Only submit if enough data is set (to prevent from submitting before session have been updated asynchronously)
        if (this.session.course['options'] && this.session.course['route-id']) {

            // If no route have been selected, display an error message
            if (this.route == undefined) showResponseMessage({error: 'ルートが選択されていません。下記のリストからルートを選択してください。'})
            // Else, validate, send data to API and go to next page
            else {
                // Update meeting place and finish place information (only if not set or having changed)
                const coursedata = {
                    'myRoutes': this.route.id,
                    'terrain': terrainDiv.innerText,
                    'distance': parseFloat(distanceDiv.innerText.substring(0, distanceDiv.innerText.length - 2)),
                    'meetingplace': this.route.meetingplace,
                    'finishplace': this.route.finishplace,
                    'course-description': document.querySelector('#courseDescriptionTextarea').value
                }
                this.updateSession( {
                    method: 'draw',
                    data: coursedata
                } ).then( () => document.getElementById('form').submit())
            }

        } else showResponseMessage({error: '必要データを全て入力してください。'})

        this.$map.addEventListener('click', hideResponseMessage, 'once')
    }

    async treatRouteChange () {
        var checkpoints = this.data.checkpoints
        const routeData = await this.getRouteData()
        const routeCoords = routeData.geometry.coordinates

        // If start or goal has changed (on route update), update it
        if (this.options && this.options.sf !== null) {
            if (this.options.sf == true) {
                // Start/Goal
                if ((checkpoints[0].lngLat.lng != routeCoords[0][0]) && (checkpoints[0].lngLat.lat != routeCoords[0][1])) {
                    checkpoints[0].lngLat = {
                        lng: routeCoords[0][0],
                        lat: routeCoords[0][1]
                    }
                    checkpoints[0].marker.setLngLat(checkpoints[0].lngLat)
                }
            } else {
                // Start
                if ([checkpoints[0].lngLat.lng, checkpoints[0].lngLat.lat] != routeCoords[0]) {
                    checkpoints[0].lngLat = {
                        lng: routeCoords[0][0],
                        lat: routeCoords[0][1]
                    }
                    checkpoints[0].marker.setLngLat(checkpoints[0].lngLat)
                }
                // Goal
                if ([checkpoints[checkpoints.length - 1].lngLat.lng, checkpoints[checkpoints.length - 1].lngLat.lat] != routeCoords[routeCoords.length - 1]) {
                    checkpoints[checkpoints.length - 1].lngLat = {
                        lng: routeCoords[routeCoords.length - 1][0],
                        lat: routeCoords[routeCoords.length - 1][1]
                    }
                    checkpoints[checkpoints.length - 1].marker.setLngLat(checkpoints[checkpoints.length - 1].lngLat)
                }
            }
        }
    }

}