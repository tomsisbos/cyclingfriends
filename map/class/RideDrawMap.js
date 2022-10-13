import CFUtils from "/map/class/CFUtils.js"
import RideMap from "/map/class/rideMap.js"
import Popup from "/map/class/Popup.js"

export default class RideDrawMap extends RideMap {

    constructor () {
        super()
    }

    route
    routeSource
    profileData = {}
    method = 'draw'

    async loadRoute (routeId) {
        
        return new Promise ( async (resolve, reject) => {
            ajaxGetRequest ('/map/routes/api.php' + "?route-load=" + routeId, async (route) => {
                this.route = route
                // Update labels and values
                var distanceDiv = document.querySelector('#distanceDiv')
                distanceDiv.innerText = Math.ceil(route.distance * 10) / 10 + 'km'
                ajaxGetRequest ('/rides/api.php' + "?get-terrain-value=" + route.id, async (response) => {
                    var terrainDiv = document.querySelector('#terrainDiv')
                    terrainDiv.innerText = response
                } )
                // If no course description data stored in database, load route description as course description by default
                if (!this.session['edit-forms'] || (this.session['edit-forms'] && !this.session['edit-forms'][2]['course-description'])) document.querySelector('#courseDescriptionTextarea').value = route.description
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
                await this.map.once('idle')
                document.querySelector('#profileBox').style.display = 'block'
                document.querySelector('#js-draw .rd-course-fields').style.paddingTop = 'calc(420px + 15vh)'
                await this.displayCloseMkpoints(0.5)
                this.addStartGoalMarkers()
                this.generateProfile()
                resolve()
            } )
        } )
    }

    async generateProfile (options = {force: false}) {
        
        const routeSource = this.map.getSource('route')

        // If route has been changed since last profile update
        if (routeSource != this.routeSource || options.force == true) {

            // If a route is displayed on the map
            if (routeSource) {

                // Prepare profile data
                this.profileData = await this.getProfileData(routeSource._data, {remote: true})
                
                // Draw profile inside elevationProfile element

                // Prepare profile settings
                const ctx = document.getElementById('elevationProfile').getContext('2d')
                const downtwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y + 2 ? value : undefined
                const flat = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 2 ? value : undefined
                const uptwo = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 6 ? value : undefined
                const upsix = (ctx, value) => ctx.p0.parsed.y > ctx.p1.parsed.y - 10 ? value : undefined
                const upten = (ctx, value) => ctx.p0.parsed.y > 0 ? value : undefined                    
                const data = {
                    labels: this.profileData.labels,
                    datasets: [ {
                        data: this.profileData.pointData,
                        fill: {
                            target: 'origin',
                            above: '#fffa9ccc'
                        },
                        borderColor: '#bbbbff',
                        tension: 0.1
                    } ],
                }
                const backgroundColor = {
                    id: 'backgroundColor',
                    beforeDraw: (chart) => {
                        const ctx = chart.canvas.getContext('2d')
                        ctx.save()
                        ctx.globalCompositeOperation = 'destination-over'
                        var lingrad = ctx.createLinearGradient(0, 0, 0, 150);
                        lingrad.addColorStop(0, '#f9f9f9');
                        lingrad.addColorStop(0.5, '#fff');
                        ctx.fillStyle = lingrad
                        ctx.fillRect(0, 0, chart.width, chart.height)
                        ctx.restore()
                    }
                }
                const displayMkpoints = {
                    id: 'displayMkpoints',
                    afterRender: (chart) => {
                        const ctx = chart.canvas.getContext('2d')
                        const routeData = routeSource._data
                        const routeDistance = turf.length(routeData)
                        if (this.mkpoints) {
                            this.mkpoints.forEach( (mkpoint) => {
                                if (mkpoint.on_route) {
                                    // Get X position
                                    const mkpointDistance = mkpoint.distance
                                    var roughPositionProportion = mkpointDistance / routeDistance * 100
                                    var roughPositionPixel = roughPositionProportion * (chart.scales.x._maxLength - chart.scales.x.left - chart.scales.x.paddingRight - chart.scales.x._margins.right) / 100
                                    mkpoint.position = roughPositionPixel + chart.scales.x.left
                                    // Get Y position
                                    const dataX = chart.scales.x.getPixelForValue(mkpoint.distance)
                                    const dataY = chart.scales.y.getPixelForValue(this.profileData.pointsElevation[Math.floor(mkpoint.distance * 10)])
                                    // Draw a line
                                    var cursorLength = 10
                                    ctx.strokeStyle = '#d6d6d6'
                                    ctx.lineWidth = 1
                                    ctx.beginPath()
                                    ctx.moveTo(mkpoint.position, dataY)
                                    ctx.lineTo(mkpoint.position, dataY - cursorLength)
                                    ctx.stroke()
                                    ctx.closePath()

                                    // Format icon
                                    var img    = document.querySelector('#mkpoint' + mkpoint.id).querySelector('img')
                                    var width  = 15
                                    var height = 15
                                    const positionX = mkpoint.position - width/2
                                    const positionY = dataY - cursorLength - height
                                    if (img.classList.contains('admin-marker')) {
                                        ctx.strokeStyle = 'yellow'
                                        ctx.lineWidth = 3
                                    }
                                    if (img.classList.contains('selected-marker')) {
                                        ctx.strokeStyle = '#ff5555'
                                        ctx.lineWidth = 3
                                    }

                                    var abstract = {}
                                    abstract.offscreenCanvas = document.createElement("canvas")
                                    abstract.offscreenCanvas.width = width
                                    abstract.offscreenCanvas.height = height
                                    abstract.offscreenContext = abstract.offscreenCanvas.getContext("2d")
                                    const ctx2 = abstract.offscreenContext
                                    ctx2.drawImage(img, 0, 0, width, height)
                                    ctx2.globalCompositeOperation = 'destination-atop'
                                    ctx2.arc(0 + width/2, 0 + height/2, width/2, 0, Math.PI * 2)
                                    ctx2.closePath()
                                    ctx2.fill()

                                    // Draw icon
                                    ctx.drawImage(abstract.offscreenCanvas, positionX, positionY)
                                    ctx.beginPath()
                                    ctx.arc(positionX + width/2, positionY + height/2, width/2, 0, Math.PI * 2)
                                    ctx.closePath()
                                    ctx.stroke()
                                }
                            } )
                        }
                    }
                }
                const cursorOnHover = {
                    id: 'cursorOnHover',
                    afterEvent: (chart, args) => {
                        var e = args.event
                        if (e.type == 'mousemove' && args.inChartArea == true) {
                            // Get relevant data
                            const dataX        = chart.scales.x.getValueForPixel(e.x)
                            const routeData = routeSource._data
                            const distance     = Math.floor(dataX * 10) / 10
                            const maxDistance  = chart.scales.x._endValue
                            const altitude     = this.profileData.pointsElevation[distance * 10]
                            // Slope
                            if (this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1]) {
                                var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10) + 1] - this.profileData.averagedPointsElevation[Math.floor(distance * 10)]
                            } else { // Only calculate on previous 100m for the last index (because no next index)
                                var slope = this.profileData.averagedPointsElevation[Math.floor(distance * 10)] - this.profileData.averagedPointsElevation[Math.floor(distance * 10) - 1]
                            }
                            // As mouse is inside route profile area
                            if (distance >= 0 && distance <= maxDistance) {
                                // Reload canvas
                                this.elevationProfile.destroy()
                                this.elevationProfile = new Chart(ctx, chartSettings)
                                // Draw a line
                                ctx.strokeStyle = 'black'
                                ctx.lineWidth = 1
                                ctx.beginPath()
                                ctx.moveTo(e.x, 0)
                                ctx.lineTo(e.x, 9999)
                                ctx.stroke()
                                // Display corresponding point on route
                                var routePoint = turf.along(routeData, distance, {units: 'kilometers'})
                                if (slope <= 2 && slope >= -2) {
                                    var circleColor = 'white'
                                } else {
                                    var circleColor = this.setSlopeStyle(slope).color
                                }
                                if (!this.map.getLayer('profilePoint')) {
                                    this.map.addLayer( {
                                        id: 'profilePoint',
                                        type: 'circle',
                                        source: {
                                            type: 'geojson',
                                            data: routePoint
                                        },
                                        paint: {
                                            'circle-radius': 5,
                                            'circle-color': circleColor
                                        }
                                    } )
                                } else {
                                    this.map.getSource('profilePoint').setData(routePoint)
                                    this.map.setPaintProperty('profilePoint', 'circle-color', circleColor)
                                }
                                // Display tooltip
                                this.clearTooltip()
                                this.drawTooltip(routeData, routePoint.geometry.coordinates[0], routePoint.geometry.coordinates[1], e.x, this.$map.offsetHeight - 90, {backgroundColor: '#ffffff'})
                                // Highlight corresponding mkpoint data
                                if (this.mkpoints && (!document.querySelector('#boxShowMkpoints') || document.querySelector('#boxShowMkpoints').checked)) {
                                    this.mkpoints.forEach( (mkpoint) => {
                                        if (document.getElementById(mkpoint.id) && mkpoint.distance < (distance + 1) && mkpoint.distance > (distance - 1)) {
                                            // Highlight preview image
                                            document.getElementById(mkpoint.id).querySelector('img').classList.add('admin-marker')
                                            // Highlight marker
                                            document.querySelector('#mkpoint' + mkpoint.id).querySelector('img').classList.add('admin-marker')
                                        } else if (document.getElementById(mkpoint.id) && mkpoint.on_route == true) {
                                            document.getElementById(mkpoint.id).querySelector('img').classList.remove('admin-marker')
                                            document.querySelector('#mkpoint' + mkpoint.id).querySelector('img').classList.remove('admin-marker')
                                        }
                                    } )
                                }
                            }
                        } else if (e.type == 'mouseout' || args.inChartArea == false) {
                            // Clear tooltip if one
                            this.clearTooltip()
                            // Reload canvas
                            this.elevationProfile.destroy()
                            this.elevationProfile = new Chart(ctx, chartSettings)
                            // Remove corresponding point on route
                            if (this.map.getLayer('profilePoint')) {
                                this.map.removeLayer('profilePoint')
                                this.map.removeSource('profilePoint')
                            }
                        }  
                    }              
                }
                const options = {
                    parsing: false,
                    animation: false,
                    maintainAspectRatio: false,
                    pointRadius: 0,
                    pointHitRadius: 0,
                    pointHoverRadius: 0,
                    events: ['mousemove', 'mouseout'],
                    segment: {
                        borderColor: ctx => downtwo(ctx, '#00e06e') || flat(ctx, 'yellow') || uptwo(ctx, 'orange') || upsix(ctx, '#ff5555') || upten(ctx, 'black'),
                    },
                    layout: {
                        padding: {
                            right: 15
                        }
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            bounds: 'data',
                            grid: {
                                color: '#00000000',
                                tickColor: 'lightgrey'
                            },
                            ticks: {
                                format: {
                                    style: 'unit',
                                    unit: 'kilometer'
                                },
                                autoSkip: true,
                                autoSkipPadding: 50,
                                maxRotation: 0
                            },
                            beginAtZero: true,
                        },
                        y: {
                            grid: {
                                borderDash: [5, 5],
                                drawTicks: false
                            },
                            ticks: {
                                format: {
                                    style: 'unit',
                                    unit: 'meter'
                                },
                                autoSkipPadding: 20,
                                padding: 8
                            }
                        }
                    },
                    interaction: {
                        mode: 'point',
                        axis: 'x',
                        intersect: false
                    },
                    plugins: {
                        legend: {
                            display: false,
                            labels: {
                                boxWidth: 100
                            }
                        },
                        // Define background color
                        backgroundColor: backgroundColor,
                        // Draw a vertical cursor on hover
                        cursorOnHover: cursorOnHover,
                        tooltip: {
                            enabled: false
                        },
                    },
                }
                const chartSettings = {
                    type: 'line',
                    data: data,
                    options: options,
                    plugins: [backgroundColor, cursorOnHover, displayMkpoints]
                }

                // Reset canvas
                if (this.elevationProfile) this.elevationProfile.destroy()
                // Bound chart to canvas
                this.elevationProfile = new Chart(ctx, chartSettings)
            }

            this.routeSource = routeSource

        }
    }

    async displayCloseMkpoints (range) {
        
        return new Promise ( async (resolve, reject) => {

            // Display close mkpoints inside the map
            ajaxGetRequest ('/map/api.php' + "?display-mkpoints=" + this.route_id, async (response) => {

                this.mkpoints = await this.getClosestMkpoints(response, range)

                // Display on map
                this.addMkpoints(this.mkpoints)
                
                // Update mkpoints cursors on profile
                this.generateProfile()
                
                // Display thumbnails
                // Get mkpoints on route number
                this.mkpoints.forEach( (mkpoint) => {
                    if (mkpoint.on_route) this.mkpointsOnRouteNumber++
                } )

                // For each mkpoint
                for (let i = 0, j = 0; i < this.mkpoints.length; i++) {
                    if (this.mkpoints[i].on_route) j++ // Count mkpoints on route

                    // Get images if needed
                    if (!this.mkpoints[i].file_blob) {
                        this.mkpoints[i].file_blob = await this.getFileBlob(this.mkpoints[i])
                    }

                    // For mkpoints located on route, build the thumbnail slider
                    if (this.mkpoints[i].on_route) {
                        // this.buildThumbnailSlider(this.mkpoints[i], j, this.mkpointsOnRouteNumber)
                    }
                    
                }

                resolve(this.mkpoints)
            } )
        } )
    }

    addMkpoints (mkpoints) {
        mkpoints.forEach( (mkpoint) => {
            if (mkpoint.on_route) {
                var content = this.setCheckpointPopupContent(mkpoint.name, mkpoint.description, {button: true})
                var marker = this.addMkpointMarker(mkpoint, content)
                var popup = marker.getPopup()
                // Update profile when a mkpoint is selected
                popup.on('open', () => {
                    // If mkpoint photo is not displayed yet
                    if (!popup.getElement().querySelector('img')) {
                        // Display mkpoint photo
                        var photoInput = document.createElement('div')
                        photoInput.className = 'checkpoint-popup-img-container'
                        photoInput.innerHTML = '<img class="checkpoint-popup-img" />'
                        popup.getElement().querySelector('.checkpointMarkerForm').before(photoInput)
                        popup.getElement().querySelector('.checkpoint-popup-img').src = 'data:image/jpeg;base64,' + mkpoint.file_blob
                        this.generateProfile({force: true})
                        // Add "addToCheckpoints" button click event handler
                        var $button = popup._content.querySelector('#addToCheckpoints')
                        $button.addEventListener('click', (e) => {
                            var $button = e.target
                            // If this mkpoint have not been added to checkpoints yet, add it
                            if ($button.innerText == 'Add to checkpoints') {
                                // Change button text
                                $button.innerText = 'Remove from checkpoints'
                                // Allow checkpoint content edition
                                var formContent = this.setCheckpointFormContent(mkpoint.name, mkpoint.description, {editable: true, button: true})
                                popup.getElement().querySelector('.checkpointMarkerForm').innerHTML = formContent
                                // Update and upload checkpoints basic data
                                // Insert new checkpoint
                                this.data.checkpoints.push( {
                                    lngLat: marker.getLngLat(),
                                    elevation: Math.floor(this.map.queryTerrainElevation(marker.getLngLat())),
                                    name: popup.getElement().querySelector('#name').value,
                                    description: popup.getElement().querySelector('#description').innerHTML,
                                    img: popup.getElement().querySelector('img').src
                                } )
                                // Sort checkpoints
                                var current = {lng: mkpoint.lng, lat: mkpoint.lat}
                                this.sortCheckpoints(this.map.getSource('route')._data)
                                var number = this.getCheckpointNumber(this.data.checkpoints, current)
                                this.updateMarkers()
                                this.updateSession( {
                                    method: this.method,
                                    data: {
                                        'checkpoints': this.data.checkpoints
                                    }
                                } )
                                // Add checkpoint element while hiding former one
                                let element = this.createCheckpointElement(number)
                                var formerElement = marker.getElement()
                                formerElement.firstChild.classList.add('hidden')
                                marker.getElement().classList.add('checkpoint-marker')
                                marker.getElement().innerHTML = formerElement.innerHTML + element.innerText
                                // Set data
                                this.setData(number)
                                this.cursor++
                            // If this mkpoint have been added to checkpoints, remove it
                            } else {
                                var number = parseInt(marker.getElement().innerText)
                                // Change button text
                                $button.innerText = 'Add to checkpoints'
                                // Disable checkpoint content edition
                                var formContent = this.setCheckpointFormContent(mkpoint.name, mkpoint.description, {editable: false, button: true})
                                popup.getElement().querySelector('.checkpointMarkerForm').innerHTML = formContent
                                // Update markers
                                this.updateMarkers()
                                // Remove checkpoint element and display former one back
                                marker.getElement().firstChild.nextSibling.remove()
                                marker.getElement().classList.remove('checkpoint-marker')
                                marker.getElement().firstChild.classList.remove('hidden')
                                // Remove this checkpoint data
                                this.data.checkpoints.splice(number, 1)
                                this.sortCheckpoints(this.map.getSource('route')._data)
                                this.updateSession( {
                                    method: this.method,
                                    data: {
                                        'checkpoints': this.data.checkpoints
                                    }
                                })
                                this.cursor--
                            }
                        } )
                    }
                    return marker 
                } )
            }
        } )
    }

    addMkpointMarker (mkpoint, content) {
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + mkpoint.thumbnail
        icon.classList.add('mkpoint-icon')
        if (mkpoint.on_route === true) icon.classList.add('oncourse-marker')
        element.appendChild(icon)
        this.map.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )

        var content = this.setCheckpointPopupContent(mkpoint.name, mkpoint.description, {editable: true})
        let popupInstance = new Popup({closeButton: false, maxWidth: '180px'}, {markerHeight: 24})
        let popup = popupInstance.popup
        popup.setHTML(content)
        marker.setPopup(popup)
        marker.setLngLat([mkpoint.lng, mkpoint.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'mkpoint' + mkpoint.id
        marker.getElement().className = 'mkpoint-marker'
        marker.getElement().dataset.id = mkpoint.id
        marker.getElement().dataset.user_id = mkpoint.user_id
        popup.on('open', (e) => {
            // Add 'selected-marker' class to selected marker
            this.unselectAllMarkers()
            document.getElementById('mkpoint' + mkpoint.id).firstChild.classList.add('selected-marker')
        } )
        popup.on('close', (e) => {
            // Remove 'selected-marker' class from selected marker if there is one
            if (document.getElementById('mkpoint' + mkpoint.id)) {
                document.getElementById('mkpoint' + mkpoint.id).firstChild.classList.remove('selected-marker')
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
            marker.getElement().addEventListener('contextmenu', this.removeOnClickHandler)
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

    async addStartGoalMarkers () {
        // If no start has been detected, add it
        if (!this.data.checkpoints[0]) {
            // Add checkpoints
            var routeCoords = this.route.coordinates
            var lineString = turf.lineString([[routeCoords[0].lng, routeCoords[0].lat], [routeCoords[routeCoords.length - 1].lng, routeCoords[routeCoords.length - 1].lat]])
            // If distance from start to goal is less than 200m, set them to the same point
            if (turf.length(lineString) < 0.2) {
                var startMarker = this.addMarker(this.route.coordinates[0])
                this.displayThumbnail(startMarker)
                var goalMarker = startMarker
                this.options.sf = true
                this.setToSF()
                var options = {
                    sf: true
                }
            } else {
                var startMarker = this.addMarker(routeCoords[0])
                this.displayThumbnail(startMarker)
                var goalMarker = this.addMarker(routeCoords[routeCoords.length - 1])
                this.displayThumbnail(goalMarker)
                this.options.sf = false
                this.setToSF()
                var options = {
                    sf: false
                }
            }
            // Update course infos
            this.sortCheckpoints(this.map.getSource('route')._data)
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
        console.log(this.data.checkpoints)
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
            console.log('i = ' + i)
        }
        return element
    }

    removeOnClick (e) {
        var number = parseInt(e.target.innerHTML)
        this.data.checkpoints[number].marker.remove()
        this.data.checkpoints.splice(number, 1)
        this.sortCheckpoints(this.map.getSource('route')._data)
        this.updateMarkers()

        // Update and upload checkpoints data to API
        this.updateSession( {
            method: this.method,
            data: {
                'checkpoints': this.data.checkpoints
            }
        })

        this.cursor--        
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

    addMarkerOnRoute (lngLat) {
        // Generate marker
        this.addMarker(lngLat)
        // Update checkpoints data
        this.sortCheckpoints(this.map.getSource('route')._data)
        this.updateMarkers()
        this.updateSession( {
            method: this.method,
            data: {
                'checkpoints': this.data.checkpoints
            }
        } )
    }

}