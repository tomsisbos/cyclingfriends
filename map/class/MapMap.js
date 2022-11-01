import CFUtils from "/map/class/CFUtils.js"
import GlobalMap from "/map/class/GlobalMap.js"
import MkpointPopup from "/map/class/MkpointPopup.js"
import RidePopup from "/map/class/RidePopup.js"
import SegmentPopup from "/map/class/SegmentPopup.js"
import TempPopup from "/map/class/TempPopup.js"

export default class MapMap extends GlobalMap {

    constructor (session) {
        super(session)
    }

    type = 'mapMap'
    cursor = 1
    tempMarkerCollection = []
    mkpointsMarkerCollection = []
    ridesCollection = []
    segmentsCollection = []
    displayMkpointsBox
    displayRidesBox
    displaySegmentsBox
    mkpointsZoomRoof = 7 // Mkpoint display minimum zoom level
    ridesZoomRoof = 6 // rides display minimum zoom level
    segmentsZoomRoof = 5 // segments display minimum zoom level
    segmentsZoomRange = {
        national: {
            min: this.segmentsZoomRoof,
            max: this.segmentsZoomRoof + 7
        },
        regional: {
            min: this.segmentsZoomRoof + 4,
            max: this.segmentsZoomRoof + 11
        },
        local: {
            min: this.segmentsZoomRoof + 6,
            max: 20
        }
    }
    segmentLocalColor = '#8bffff'
    segmentRegionalColor = '#2bffff'
    segmentNationalColor = '#2bc8ff'
    segmentCapColor = 'white'
    rideColor = 'yellow'
    rideCapColor = 'white'
    capColorHover = '#ff5555'
    mode = 'default'
    highlight = false

    updateMapDataListener = this.updateMapData.bind(this)
    updateMapData () {
        if (this.displayMkpointsBox.checked) this.updateMkpoints()
        if (this.displayRidesBox.checked) this.updateRides()
        if (this.displaySegmentsBox.checked) this.updateSegments()
    }

    setMkpoint (mkpoint) {        
        // Add marker to the map and to markers collection
        let mkpointPopup = new MkpointPopup()
        if (this.inViewedMkpointsList(mkpoint)) mkpointPopup.activity_id = this.inViewedMkpointsList(mkpoint)
        var content = mkpointPopup.setPopupContent(mkpoint)
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + mkpoint.thumbnail
        icon.classList.add('mkpoint-icon')
        if (mkpointPopup.activity_id) element.classList.add('moving-marker') // Highlight if visited
        element.appendChild(icon)
        this.map.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )
        // If connected user is administrator of this mkpoint
        if (mkpoint.user_id == this.session.id) {
            var mkpointAdminPanel = `
                <div id="mkpointAdminPanel" class="popup-content container-admin">
                    <div class="popup-head">Edition tools</div>
                    <div class="popup-buttons">
                        <button class="mp-button bg-button text-white" id="mkpointEdit">Edit</button>
                        <button class="mp-button bg-button text-white" id="mkpointMove">Move</button>
                        <button class="mp-button bg-danger text-white" id="mkpointDelete">Delete</button>
                    </div>
                </div>`
            // Insert admin panel before the popup content
            var index = content.indexOf('<div id="popup-content"')
            content = content.slice(0, index) + mkpointAdminPanel + content.slice(index)
        }

        let popup = mkpointPopup.popup
        popup.setHTML(content)
        mkpointPopup.data = mkpoint
        if (this.inViewedMkpointsList(mkpoint)) mkpointPopup.visited = true
        marker.setPopup(popup)
        marker.setLngLat([mkpoint.lng, mkpoint.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'mkpoint' + mkpoint.id
        marker.getElement().classList.add('mkpoint-marker')
        marker.getElement().dataset.id = mkpoint.id
        marker.getElement().dataset.user_id = mkpoint.user_id
        popup.on('open', (e) => {
            // Add 'selected-marker' class to selected marker
            this.unselect()
            mkpointPopup.select()
            mkpointPopup.comments()
            mkpointPopup.rating()
            if (content.includes('mkpointAdminPanel')) mkpointPopup.mkpointAdmin()
            if (content.includes('target-button')) mkpointPopup.setTarget()
            if (content.includes('addphoto-button')) mkpointPopup.addPhoto()
            if (content.includes('round-propic-img')) mkpointPopup.addPropic()
        } )
        popup.on('close', (e) => {
            // Remove 'selected-marker' class from selected marker if there is one
            if (document.getElementById('mkpoint' + mkpointPopup.data.id)) {
                document.getElementById('mkpoint' + mkpointPopup.data.id).querySelector('.mkpoint-icon').classList.remove('selected-marker')
            }
        } )
        // Set markerpoint to draggable depending on if user is marker admin and has set edit mode to true or not
        if (mkpoint.user_id === this.session.id && this.mode == 'edit') marker.setDraggable(true)
        else if (mkpoint.user_id === this.session.id && this.mode == 'default') marker.setDraggable(false)

        marker.popularity = mkpoint.popularity // Append popularity data to the marker allowing popularity zoom filtering
        this.mkpointsMarkerCollection.push(marker)
    }

    updateMkpoints () {
        if (this.map.getZoom() > this.mkpointsZoomRoof) {
            const bounds = this.map.getBounds()
            ajaxGetRequest (this.apiUrl + "?display-mkpoints=true", (mkpoints) => {

                // First, remove all mkpoints that have left bounds
                var collection = this.mkpointsMarkerCollection
                let i = 0
                while (i < collection.length) {
                    // If existing marker is not inside new bounds OR should not be displayed at this zoom level
                    if ((!(collection[i]._lngLat.lat < bounds._ne.lat && collection[i]._lngLat.lat > bounds._sw.lat) || !(collection[i]._lngLat.lng < bounds._ne.lng && collection[i]._lngLat.lng > bounds._sw.lng)) || !this.zoomPopularityFilter(collection[i].popularity)) {
                        collection[i].remove() // Remove it from the DOM
                        collection.splice(i, 1) // Remove it from instance Nodelist
                        i--
                    }
                    i++
                }

                // Second, add all mkpoints that have entered bounds
                mkpoints.forEach( (mkpoint) => {
                    // If mkpoint is inside bounds
                    if ((mkpoint.lat < bounds._ne.lat && mkpoint.lat > bounds._sw.lat) && (mkpoint.lng < bounds._ne.lng && mkpoint.lng > bounds._sw.lng)) {
                        // Verify it has not already been loaded
                        if (!document.querySelector('#mkpoint' + mkpoint.id)) {
                            // Filter through zoom popularity algorithm
                            if (this.zoomPopularityFilter(mkpoint.popularity) == true) {
                                this.setMkpoint(mkpoint)
                            }
                        }
                    }
                } )

                // Update mkpoints scale
                document.querySelectorAll('.mkpoint-icon').forEach((mkpointIcon) => this.map.scaleMarkerAccordingToZoom(mkpointIcon))
            } )

        } else {
            for (let i = 0; i < this.mkpointsMarkerCollection.length; i++) this.mkpointsMarkerCollection[i].remove()
            this.mkpointsMarkerCollection = []
        }
    }

    zoomPopularityFilter (popularity) {

        const zoom = this.map.getZoom()

        // Define zoom levels
        const fullDisplayZone  = 6 // Range of zoom levels starting maxZoomLevel which all mkpoints will be displayed
        const maxZoomLevel     = 22 // Maximum zoom level of map provider (22 for Mapbox)
        const zoomLevel0       = maxZoomLevel - fullDisplayZone // Zoom level from which all mkpoints will be displayed
        const zoomRange        = zoomLevel0 - this.mkpointsZoomRoof
        if (zoomRange <= 0) return true // zoomRange can't be negative (don't filter anything in this case)
        const zoomStep = zoomRange / 4
        var zoomLevel1 = zoomLevel0 - zoomStep
        var zoomLevel2 = zoomLevel1 - zoomStep
        var zoomLevel3 = zoomLevel2 - zoomStep
        var zoomLevel4 = this.mkpointsZoomRoof

        // Define popularity levels
        var popularityLevel4 = 110
        var popularityLevel3 = 60
        var popularityLevel2 = 30
        var popularityLevel1 = 0

        // Over upper limit
        if (zoom < this.mkpointsZoomRoof) {
            return false
        }

        // Level 4
        else if (zoom > zoomLevel4 && zoom < zoomLevel3) {
            if (popularity > popularityLevel4) {
                return true
            } else {
                return false
            }
        }

        // Level 3
        else if (zoom > zoomLevel3 && zoom < zoomLevel2) {
            if (popularity > popularityLevel3) {
                return true
            } else {
                return false
            }
        }
        
        // Level 2
        else if (zoom > zoomLevel2 && zoom < zoomLevel1) {
            if (popularity > popularityLevel2) {
                return true
            } else {
                return false
            }
        }

        // Level 1
        else if (zoom > zoomLevel1 && zoom < zoomLevel0) {
            if (popularity > popularityLevel1) {
                return true
            } else {
                return false
            }
        }

        // Down lower limit
        else {
            return true
        }

    }

    updateRides () {

        // If current zoom is precise enough
        if (this.map.getZoom() > this.ridesZoomRoof) {
            ajaxGetRequest (this.apiUrl + "?display-rides=true", (rides) => {

                // First, remove all rides that have left bounds
                let i = 0
                while (i < this.ridesCollection.length) {
                    // If existing ride is not inside new bounds
                    if (!CFUtils.isInsideBounds(this.map.getBounds(), this.ridesCollection[i].route)) {
                        if (this.map.getLayer('ride' + this.ridesCollection[i].id)) this.hideRide(this.ridesCollection[i]) // Remove it from the map
                        this.ridesCollection.splice(i, 1) // Remove it from instance Nodelist
                        i--
                    }
                    i++
                }

                // Second, add all rides that have entered bounds
                rides.forEach( (ride) => {
                    // If ride is public and has a route data
                    if (ride.privacy == 'Public' && ride.route) {
                        // If ride is inside bounds
                        if (CFUtils.isInsideBounds(this.map.getBounds(), ride.route)) {
                            // Verify it has not already been loaded
                            if (!this.isLinestringAlreadyDisplayed(ride)) {
                                this.ridesCollection.push(ride)
                                this.displayRide(ride)
                            }
                        }
                    }
                } )
            } )

        // If current zoom is not precise enough
        } else {
            // Hide all rides and clear instance property
            this.ridesCollection.forEach( (ride) => {
                if (this.map.getLayer('ride' + ride.id)) this.hideRide(ride)
            } )
            this.ridesCollection = []
        }
    }

    displayRide (ride) {

        // Build geojson
        var geojson = {
            type: 'Feature',
            properties: {
                name: ride.name,
                date: ride.date,
                author: ride.author_login
            },
            geometry: {
                type: 'LineString',
                coordinates: ride.route
            }
        }

        // Add source
        this.map.addSource('ride' + ride.id, {
            type: 'geojson',
            data: geojson
        } )

        // Add ride cap layer
        this.map.addLayer( {
            id: 'rideCap' + ride.id,
            type: 'line',
            source: 'ride' + ride.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.rideCapColor,
                'line-width': 2,
                'line-opacity': 0,
                'line-gap-width': 2
            }
        } )

        // Add ride layer
        this.map.addLayer( {
            id: 'ride' + ride.id,
            type: 'line',
            source: 'ride' + ride.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.rideColor,
                'line-width': 3,
                'line-opacity': 1
            }
        } )
        
        // Set animation
        this.map.on('mouseenter', 'rideCap' + ride.id, () => {
            this.map.getCanvas().style.cursor = 'pointer'
            this.map.setPaintProperty('rideCap' + ride.id, 'line-opacity', 1)
        } )

        this.map.on('mouseleave', 'rideCap' + ride.id, () => {
            this.map.getCanvas().style.cursor = 'grab'

            // Get newest ride data from instance collection
            this.ridesCollection.forEach((entry) => {
                if (ride.id == entry.id) ride = entry
            } )

            // Leave cap displayed when popup is open
            if ((!ride.ridePopup || (ride.ridePopup && !ride.ridePopup.popup.isOpen())) && this.map.getLayer('rideCap' + ride.id)) {
                this.map.setPaintProperty('rideCap' + ride.id, 'line-opacity', 0)
            }
        } )

        this.map.on('click', 'rideCap' + ride.id, this.clickOnRide)
    }

    clickOnRide = (e) => {
        
        // Don't open if there is another feature on top
        if (this.map.queryRenderedFeatures(e.point)[0].source.includes('ride')) {

            // Get ride from ridesCollection using layer ID
            var ride
            this.ridesCollection.forEach(entry => {
                if (entry.id == getIdFromString(e.features[0].source)) ride = entry
            } )
            ride.ridePopup = new RidePopup( {
                closeOnClick: true,
                anchor: 'bottom',
                className: 'js-linestring marker-popup js-ride-popup'
            }, ride)
            ride.ridePopup.popup.setLngLat(ride.route[0])

            // Color ride cap in hovering style
            this.map.setPaintProperty('rideCap' + ride.id, 'line-color', this.capColorHover)
            
            // Remove popup instance and hide ride cap when popup is closed
            ride.ridePopup.popup.on('close', () => {
                delete ride.ridePopup
                if (this.map.getLayer('rideCap' + ride.id)) {
                    this.map.setPaintProperty('rideCap' + ride.id, 'line-opacity', 0)
                    this.map.setPaintProperty('rideCap' + ride.id, 'line-color', this.rideCapColor)
                }
            } )

            ride.ridePopup.popup.addTo(this.map)
            
            // Hide cap when popup is closed
            ride.ridePopup.popup.on('close', () => {
                if (this.map.getLayer('rideCap' + ride.id)) {
                    this.map.setPaintProperty('rideCap' + ride.id, 'line-opacity', 0)
                    this.map.setPaintProperty('rideCap' + ride.id, 'line-color', this.rideCapColor)
                }
            } )

            // Dislpay featured image
            this.displayFeaturedImage(ride)

            // Update rideCollection entry
            this.ridesCollection.forEach((entry) => {
                if (ride.id == entry.id) entry = ride
            } )

            // Focus on ride
            this.focus(this.map.getSource('ride' + ride.id)._data)
        }
    }

    hideRide (ride) {
        this.map.removeLayer('ride' + ride.id)
        this.map.removeLayer('rideCap' + ride.id)
        this.map.removeSource('ride' + ride.id)
        this.map.off('click', 'rideCap' + ride.id, this.clickOnRide)
        if (ride.ridePopup && ride.ridePopup.popup) ride.ridePopup.popup.remove()
    }

    displayFeaturedImage (ride) {
        ajaxGetRequest (this.apiUrl + "?ride-featured-image=" + ride.id, (featuredCheckpoint) => {
            document.querySelector('#rideFeaturedImage' + ride.id).src = 'data:image/jpeg;base64,' + featuredCheckpoint.img
        } )

    }

    updateSegments () {

        // If current zoom is precise enough
        if (this.map.getZoom() > this.segmentsZoomRoof) {
            ajaxGetRequest (this.apiUrl + "?display-segments=true", (segments) => {

                // First, remove all segments that have left bounds
                let i = 0
                while (i < this.segmentsCollection.length) {
                    // If existing segment is not inside new bounds, or if it is not displayable at this zoom level
                    if (!CFUtils.isInsideBounds(this.map.getBounds(), this.segmentsCollection[i].route.coordinates) || !this.isSegmentToDisplay(this.segmentsCollection[i])) {
                        if (this.map.getLayer('segment' + this.segmentsCollection[i].id)) this.hideSegment(this.segmentsCollection[i]) // Remove it from the map
                        this.segmentsCollection.splice(i, 1) // Remove it from instance Nodelist
                        i--
                    }
                    i++
                }

                // Second, add all segments that have entered bounds
                segments.forEach( (segment) => {
                    // If segment is public and has a route data
                    if (this.isSegmentToDisplay(segment)) {
                        // If segment is inside bounds
                        if (CFUtils.isInsideBounds(this.map.getBounds(), segment.route.coordinates)) {
                            // Verify it has not already been loaded
                            if (!this.isLinestringAlreadyDisplayed(segment)) {
                                this.segmentsCollection.push(segment)
                                this.displaySegment(segment)
                            }
                        }
                    }
                } )
            } )

        // If current zoom is not precise enough
        } else {
            // Hide all segments and clear instance property
            this.segmentsCollection.forEach( (segment) => {
                if (this.map.getLayer('segment' + segment.id)) this.hideSegment(segment)
            } )
            this.segmentsCollection = []
        }
    }

    displaySegment (segment) {

        // Build geojson
        var geojson = {
            type: 'Feature',
            properties: {
                rank: segment.rank,
                name: segment.name,
                specs: {
                    offroad: segment.spec_offroad,
                    rindo: segment.spec_rindo
                },
                tags: {
                    hanami: segment.spec_hanami,
                    kouyou: segment.kouyou,
                    ajisai: segment.ajisai,
                    culture: segment.spec_culture,
                    machinami: segment.spec_machinami,
                    shrines: segment.spec_shrines,
                    teaFields: segment.spec_tea_fields,
                    sea: segment.spec_sea,
                    mountains: segment.spec_mountains,
                    forest: segment.spec_forest,
                },
                tunnels: segment.route.tunnels
            },
            geometry: {
                type: 'LineString',
                coordinates: segment.route.coordinates
            }
        }

        // Add source
        this.map.addSource('segment' + segment.id, {
            type: 'geojson',
            data: geojson
        } )

        // Add segment cap layer
        this.map.addLayer( {
            id: 'segmentCap' + segment.id,
            type: 'line',
            source: 'segment' + segment.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.segmentCapColor,
                'line-width': 2,
                'line-opacity': 0,
                'line-gap-width': 2
            }
        } )

        // Define segment color
        if (segment.rank == 'local') var segmentColor = this.segmentLocalColor
        if (segment.rank == 'regional') var segmentColor = this.segmentRegionalColor
        if (segment.rank == 'national') var segmentColor = this.segmentNationalColor

        // Add segment layer
        this.map.addLayer( {
            id: 'segment' + segment.id,
            type: 'line',
            source: 'segment' + segment.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': segmentColor,
                'line-width': 3,
                'line-opacity': 1
            }
        } )

        // Set animation
        this.map.on('mouseenter', 'segmentCap' + segment.id, () => {
            this.map.getCanvas().style.cursor = 'pointer'
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 1)
        } )
        this.map.on('mouseleave', 'segmentCap' + segment.id, () => {
            this.map.getCanvas().style.cursor = 'grab'

            // Get newest segment data from instance collection
            this.segmentsCollection.forEach((entry) => {
                if (segment.id == entry.id) segment = entry
            } )

            // Leave cap displayed when segment is open
            if ((!segment.segmentPopup || (segment.segmentPopup && !segment.segmentPopup.popup.isOpen())) && this.map.getLayer('segmentCap' + segment.id)) {
                this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 0)
            }
        } )
        this.map.on('click', 'segmentCap' + segment.id, this.clickOnSegment)
    }

    clickOnSegment = async (e) => {

        // Don't open if there is another feature on top
        if (this.map.queryRenderedFeatures(e.point)[0].source.includes('segment')) {

            // Get segment from segmentsCollection using layer ID
            var segment
            this.segmentsCollection.forEach(entry => {
                if (entry.id == getIdFromString(e.features[0].source)) segment = entry
            } )
            
            // Create segment popup instance
            segment.segmentPopup = new SegmentPopup( {
                closeOnClick: true,
                anchor: 'bottom',
                className: 'js-linestring marker-popup js-segment-popup'
            }, segment)

            // Prepare and display segment popup
            const popup = segment.segmentPopup.popup
            popup.setLngLat(segment.route.coordinates[0])
            popup.addTo(this.map)
            segment.segmentPopup.rating()
            segment.segmentPopup.generateProfile({force: true})
            segment.segmentPopup.addIconButtons()
            popup.getElement().querySelector('#fly-button').addEventListener('click', async () => {
                this.map.off('moveend', this.updateMapDataListener)
                await this.flyAlong(turf.lineString(segment.route.coordinates))
                this.map.on('moveend', this.updateMapDataListener)
            } )

            // Color segment cap in hovering style
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.capColorHover)
            
            // Remove instance and hide segment cap when popup is closed
            popup.on('close', () => {
                delete segment.segmentPopup
                if (this.map.getLayer('segmentCap' + segment.id)) {
                    this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 0)
                    this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.segmentCapColor)
                }
            } )

            /*
            // Dislpay featured image
            this.displayFeaturedImage(segment) */

            // Update segmentsCollection entry
            this.segmentsCollection.forEach((entry) => {
                if (segment.id == entry.id) entry = segment
            } )

            // Focus on segment
            this.focus(this.map.getSource('segment' + segment.id)._data)
            
            // Add segment relevant photos
            segment.segmentPopup.mkpoints = await segment.segmentPopup.getMkpoints()
            segment.segmentPopup.displayPhotos()
        }
    }

    hideSegment (segment) {
        this.map.removeLayer('segment' + segment.id)
        this.map.removeLayer('segmentCap' + segment.id)
        this.map.removeSource('segment' + segment.id)
        this.map.off('click', 'segmentCap' + segment.id, this.clickOnSegment)
        if (segment.segmentPopup && segment.segmentPopup.popup) segment.segmentPopup.popup.remove()
    }

    isSegmentToDisplay (segment) {
        if (this.map.getZoom() > this.segmentsZoomRange[segment.rank].min && this.map.getZoom() < this.segmentsZoomRange[segment.rank].max) return true
        else return false
    }

    isLinestringAlreadyDisplayed (linestring) {
        var isLinestring = false
        this.ridesCollection.forEach( (entry) => {
            if ((entry.id == linestring.id) && (entry.name == linestring.name)) isLinestring = true
        } )
        this.segmentsCollection.forEach( (entry) => {
            if ((entry.id == linestring.id) && (entry.name == linestring.name)) isLinestring = true
        } )
        return isLinestring
    }


    /* Edit mode */

    addTempMarker (lngLat, elevation) {
        var marker = new mapboxgl.Marker(
            {
                color: '#f6b9cd',
                draggable: true,
                scale: 0.8
            }
        )
        marker.elevation = elevation
        marker.setLngLat(lngLat)

        var tempPopup = new TempPopup()
        var popup = tempPopup.popup
        popup.setHTML(`
            <div class="popup-head popup-content container-admin">Share a new scenery spot</div>
            <form class="popup-content" name="mkpointForm" id="mkpointForm">
                <strong>Name :</strong>
                <input type="text" name="name" class="admin-field"/>
                <strong>Description :</strong>
                <textarea name="description" class="admin-field"></textarea>
                <input type="hidden" name="MAX_FILE_SIZE" value="10000000" />
                <label for="file" class="mp-button"><input enctype="multipart/form-data" type="file" name="file" id="file" style="display: none" />Upload a photo</label>
                <img class="mp-image-preview" />
                <input type="submit" name="mbkPointForm" value="Submit" class="mp-button bg-button text-white fullwidth" />
            </form>`)
        marker.setPopup(popup)
        popup.on('open', async () => {
            // Display a preview on photo upload
            var file = document.getElementById('file')
            var previewImage = document.querySelector('.mp-image-preview')
            file.addEventListener('change', (e) => {
                previewImage.src = URL.createObjectURL(e.target.files[0])
            } )
            // Save data on submit and display new data
            var save = tempPopup.save.bind(tempPopup)
            await save()
            this.updateMkpoints()
        } )
        
        popup.options.className = 'hidden' // Hide popup as creating in edit mode
        marker.addTo(this.map)

        let $marker = marker.getElement()
        $marker.id = 'marker' + this.cursor
        this.cursor++
        this.tempMarkerCollection.push(marker)

        return marker
    }

    // When assignated to a listener, remove the marker having been targetted by the event
    removeOnClick (e) {
        e.preventDefault()
        e.target.closest('.mapboxgl-marker').remove()
        this.cursor--
    }

    clearMapData () {
        // Remove rides
        this.ridesCollection.forEach( (ride) => this.hideRide(ride))
        this.ridesCollection = []
        // Remove segments
        this.segmentsCollection.forEach( (segment) => this.hideSegment(segment))
        this.segmentsCollection = []
    }
}