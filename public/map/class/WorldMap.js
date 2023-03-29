import CFUtils from "/map/class/CFUtils.js"
import CFSession from "/map/class/CFSession.js"
import WorldHelper from "/scripts/helpers/map.js"
import GlobalMap from "/map/class/GlobalMap.js"
import SceneryPopup from "/map/class/scenery/SceneryPopup.js"
import RidePopup from "/map/class/ride/RidePopup.js"
import SegmentPopup from "/map/class/segment/SegmentPopup.js"
import TempPopup from "/map/class/TempPopup.js"

export default class WorldMap extends GlobalMap {

    constructor (options) {
        super(options)
    }

    type = 'worldMap'
    data = {}
    cursor = 1
    tempMarkerCollection = []
    mkpointsMarkerCollection = []
    ridesCollection = []
    segmentsCollection = []
    displayMkpointsBox
    displayRidesBox
    displaySegmentsBox
    mkpointsZoomRoof = 7 // Mkpoint display minimum zoom level
    mkpointsMinNumber = 10 // Number of mkpoints displayed to try to reach at minimum
    mkpointsMaxNumber = 20 // Maximum number of mkpoints displayed at the same time
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
    rideColor = 'yellow'
    rideCapColor = 'white'
    capColorHover = '#ff5555'
    mode = 'default'
    highlight = false

    loadMkpoints () {
        ajaxGetRequest (this.apiUrl + "?display-mkpoints=details", (mkpoints) => {
            this.data.mkpoints = mkpoints
        } )
    }

    updateMapData () {
        if (!this.displayMkpointsBox || this.displayMkpointsBox.checked) this.updateMkpoints()
        if (!this.displayRidesBox || this.displayRidesBox.checked) this.updateRides()
        if (!this.displaySegmentsBox || this.displaySegmentsBox.checked) this.updateSegments()
    }
    updateMapDataListener = () => this.updateMapData()

    addStyleControl () {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
        else var controller = this.addController()
        // Add style control
        var selectStyleContainer = document.createElement('div')
        selectStyleContainer.className = 'map-controller-block bold'
        controller.appendChild(selectStyleContainer)
        var selectStyleLabel = document.createElement('div')
        selectStyleLabel.innerText = '地図 : '
        selectStyleContainer.appendChild(selectStyleLabel)
        this.selectStyle = document.createElement('select')
        var seasons = document.createElement("option")
        var satellite = document.createElement("option")
        seasons.value = 'seasons'
        seasons.text = '季節'
        seasons.setAttribute('selected', 'selected')
        seasons.id = 'cl07xga7c002616qcbxymnn5z'
        satellite.id = 'cl0chu1or003a15nocgiodiir'
        satellite.value = 'satellite'
        satellite.text = '航空写真'
        this.selectStyle.add(seasons)
        this.selectStyle.add(satellite)
        this.selectStyle.className = 'js-map-styles'
        selectStyleContainer.appendChild(this.selectStyle)
        this.selectStyle.onchange = (e) => {
            var index = e.target.selectedIndex
            var layerId = e.target.options[index].id
            if (layerId === 'seasons') layerId = this.season
            this.clearMapData()
            this.setMapStyle(layerId)
            this.map.once('idle', () => this.updateMapData())
        }
    }

    addOptionsControl () {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
        else var controller = this.addController()
        // Map options
        var optionsContainer = document.createElement('div')
        optionsContainer.className = 'map-controller-block flex-column'
        controller.appendChild(optionsContainer)
        // Label
        var optionsLabel = document.createElement('div')
        optionsLabel.innerText = '設定'
        optionsLabel.className = 'map-controller-label'
        optionsContainer.appendChild(optionsLabel)
        // Line 1
        let line1 = document.createElement('div')
        line1.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line1)
        this.displayMkpointsBox = document.createElement('input')
        this.displayMkpointsBox.id = 'displayMkpointsBox'
        this.displayMkpointsBox.setAttribute('type', 'checkbox')
        this.displayMkpointsBox.setAttribute('checked', 'checked')
        line1.appendChild(this.displayMkpointsBox)
        var displayMkpointsBoxLabel = document.createElement('label')
        displayMkpointsBoxLabel.setAttribute('for', 'displayMkpointsBox')
        displayMkpointsBoxLabel.innerText = '絶景スポットを表示'
        line1.appendChild(displayMkpointsBoxLabel)
        this.displayMkpointsBox.addEventListener('change', () => {
            if (this.displayMkpointsBox.checked) this.updateMkpoints()
            else this.hideMkpoints()
        } )
        // Line 2
        let line2 = document.createElement('div')
        line2.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line2)
        this.displayRidesBox = document.createElement('input')
        this.displayRidesBox.id = 'displayRidesBox'
        this.displayRidesBox.setAttribute('type', 'checkbox')
        this.displayRidesBox.setAttribute('checked', 'true')
        line2.appendChild(this.displayRidesBox)
        this.displayRidesBox.addEventListener('click', () => {
            if (this.displayRidesBox.checked) this.updateRides()
            else this.ridesCollection.forEach( (ride) => {
                if (this.map.getLayer('ride' + ride.id)) this.hideRide(ride)
            } )
            this.ridesCollection = []
        } )
        var displayRidesBoxLabel = document.createElement('label')
        displayRidesBoxLabel.setAttribute('for', 'displayRidesBox')
        displayRidesBoxLabel.innerText = 'ライドを表示'
        line2.appendChild(displayRidesBoxLabel)
        // Line 3
        let line3 = document.createElement('div')
        line3.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line3)
        this.displaySegmentsBox = document.createElement('input')
        this.displaySegmentsBox.id = 'displaySegmentsBox'
        this.displaySegmentsBox.setAttribute('type', 'checkbox')
        this.displaySegmentsBox.setAttribute('checked', 'true')
        line3.appendChild(this.displaySegmentsBox)
        this.displaySegmentsBox.addEventListener('click', () => {
            if (this.displaySegmentsBox.checked) this.updateSegments()
            else this.segmentsCollection.forEach( (segment) => {
                if (this.map.getLayer('segment' + segment.id)) this.hideSegment(segment)
            } )
            this.segmentsCollection = []
        } )
        var displaySegmentsBoxLabel = document.createElement('label')
        displaySegmentsBoxLabel.setAttribute('for', 'displaySegmentsBox')
        displaySegmentsBoxLabel.innerText = 'セグメントを表示'
        line3.appendChild(displaySegmentsBoxLabel)
        // Line 4
        let line4 = document.createElement('div')
        line4.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line4)
        this.dislayKonbinisBox = document.createElement('input')
        this.dislayKonbinisBox.id = 'dislayKonbinisBox'
        this.dislayKonbinisBox.setAttribute('type', 'checkbox')
        this.dislayKonbinisBox.setAttribute('checked', 'true')
        line4.appendChild(this.dislayKonbinisBox)
        this.dislayKonbinisBox.addEventListener('click', () => {
            if (this.dislayKonbinisBox.checked) this.addKonbiniLayers()
            else this.hideKonbiniLayers()
        } )
        var dislayKonbinisBoxLabel = document.createElement('label')
        dislayKonbinisBoxLabel.setAttribute('for', 'dislayKonbinisBox')
        dislayKonbinisBoxLabel.innerText = 'コンビニを表示'
        line4.appendChild(dislayKonbinisBoxLabel)
        // Line 5
        let line5 = document.createElement('div')
        line5.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line5)
        this.displayAmenitiesBox = document.createElement('input')
        this.displayAmenitiesBox.id = 'dislayKonbinisBox'
        this.displayAmenitiesBox.setAttribute('type', 'checkbox')
        this.displayAmenitiesBox.setAttribute('checked', 'true')
        line5.appendChild(this.displayAmenitiesBox)
        this.displayAmenitiesBox.addEventListener('click', () => {
            if (this.displayAmenitiesBox.checked) this.addAmenityLayers()
            else this.hideAmenityLayers()
        } )
        var displayAmenitiesBoxLabel = document.createElement('label')
        displayAmenitiesBoxLabel.setAttribute('for', 'displayAmenitiesBox')
        displayAmenitiesBoxLabel.innerText = 'アメニティを表示'
        line5.appendChild(displayAmenitiesBoxLabel)
        
        // Hide and open on click on mobile display
        optionsLabel.addEventListener('click', () => {
            optionsContainer.querySelectorAll('.map-controller-line').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    optionsLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )
    }

    addFilterControl () {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
        else var controller = this.addController()
        // Filter options
        var filterContainer = document.createElement('div')
        filterContainer.className = 'map-controller-block flex-column'
        controller.appendChild(filterContainer)
        // Label
        var filterLabel = document.createElement('div')
        filterLabel.innerText = 'フィルター'
        filterLabel.className = 'map-controller-label'
        filterContainer.appendChild(filterLabel)
        // Line 1
        let line1 = document.createElement('div')
        line1.className = 'map-controller-line hide-on-mobiles'
        filterContainer.appendChild(line1)
        this.seasonsSelect = document.createElement('select')
        this.seasonsSelect.id = 'seasonsSelect'
        var options = {}
        for (let month = 1; month <= 12; month++) {
            options[month] = document.createElement('option')
            options[month].innerText = capitalizeFirstLetter(CFUtils.getMonth(month))
            options[month].value = month
            if (month == this.month) options[month].setAttribute('selected', 'selected')
            this.seasonsSelect.appendChild(options[month])
        }
        var seasonsSelectLabel = document.createElement('label')
        seasonsSelectLabel.setAttribute('for', 'seasonsSelect')
        seasonsSelectLabel.innerText = '季節'
        line1.appendChild(seasonsSelectLabel)
        line1.appendChild(this.seasonsSelect)
        this.seasonsSelect.addEventListener('change', () => {
            this.month = this.seasonsSelect.value
            this.setSeason()
            if (this.selectStyle.value == 'seasons') this.styleSeason()
        } )
        
        // Hide and open on click on mobile display
        filterLabel.addEventListener('click', () => {
            filterContainer.querySelectorAll('.map-controller-line').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    filterLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )
    }

    addEditorControl () {
        // Get (or add) controller container
        if (this.$map.querySelector('.map-controller')) var controller = this.$map.querySelector('.map-controller')
        else var controller = this.addController()
        // Container
        var editorContainer = document.createElement('div')
        editorContainer.className = 'map-controller-block flex-column bg-admin'
        controller.appendChild(editorContainer)
        // Label
        var editorLabel = document.createElement('div')
        editorLabel.innerText = '管理者設定'
        editorLabel.className = 'map-controller-label'
        editorContainer.appendChild(editorLabel)
        // Line 1
        let line1 = document.createElement('div')
        line1.className = 'map-controller-line hide-on-mobiles'
        editorContainer.appendChild(line1)
        var editModeBox = document.createElement('input')
        editModeBox.id = 'editModeBox'
        editModeBox.setAttribute('type', 'checkbox')
        line1.appendChild(editModeBox)
        editModeBox.addEventListener('click', async () => {
            if (editModeBox.checked) await WorldHelper.onEditMkpointsStart()
            this.editMode()
        } ) // Data treatment
        var editModeBoxLabel = document.createElement('label')
        editModeBoxLabel.setAttribute('for', 'editModeBox')
        editModeBoxLabel.innerText = '絶景スポットを編集'
        line1.appendChild(editModeBoxLabel)
        // Line 2
        let line2 = document.createElement('div')
        line2.className = 'map-controller-line hide-on-mobiles'
        editorContainer.appendChild(line2)
        var highlightMyMkpointsBox = document.createElement('input')
        highlightMyMkpointsBox.id = 'highlightMyMkpointsBox'
        highlightMyMkpointsBox.setAttribute('type', 'checkbox')
        line2.appendChild(highlightMyMkpointsBox)
        highlightMyMkpointsBox.addEventListener('click', () => {
            this.highlightMyMkpointsMode()
        } ) // Data treatment
        var highlightMyMkpointsBoxLabel = document.createElement('label')
        highlightMyMkpointsBoxLabel.setAttribute('for', 'highlightMyMkpointsBox')
        highlightMyMkpointsBoxLabel.innerText = '自分の絶景スポットを表示'
        line2.appendChild(highlightMyMkpointsBoxLabel)
        
        // Hide and open on click on mobile display
        editorLabel.addEventListener('click', () => {
            editorContainer.querySelectorAll('.map-controller-line').forEach( (line) => {
                if (getComputedStyle(controller).flexDirection == 'row') {
                    editorLabel.classList.toggle('up')
                    line.classList.toggle('hide-on-mobiles')
                }
            } )
        } )
    }

    async setMkpoint (mkpoint) {
        
        // Build element
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + mkpoint.thumbnail
        icon.classList.add('mkpoint-icon')
        if (mkpoint.isCleared) element.classList.add('visited-marker') // Highlight if visited
        if (mkpoint.isFavorite) element.classList.add('favoured-marker') // Highlight if favoured
        element.appendChild(icon)
        this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )
        marker.popularity = mkpoint.popularity // Append popularity data to the marker allowing popularity zoom filtering
        marker.isFavorite = mkpoint.isFavorite // Append favorites list data
        marker.setLngLat([mkpoint.lng, mkpoint.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'mkpoint' + mkpoint.id
        marker.getElement().classList.add('mkpoint-marker')
        marker.getElement().dataset.id = mkpoint.id
        marker.getElement().dataset.user_id = mkpoint.user_id
        this.mkpointsMarkerCollection.push(marker)

        // Build and attach popup
        var popupOptions = {
            closeOnMove: false
        }
        var instanceOptions = {}
        var instanceData = {
            mapInstance: this,
            mkpoint
        }
        var sessionId = await CFSession.get('id')
        if (mkpoint.user_id == sessionId) instanceOptions.admin = true // Add administration panel if connected user has admin rights
        let sceneryPopup = new SceneryPopup(popupOptions, instanceData, instanceOptions)
        marker.setPopup(sceneryPopup.popup)

        // Display mkpoint name on hover
        element.setAttribute('data-before', mkpoint.name)
        element.style.setProperty('--scenery-hover-display', 'none')
        element.addEventListener('mouseenter', () => element.style.setProperty('--scenery-hover-display', 'block'))
        element.addEventListener('mouseleave', () => element.style.setProperty('--scenery-hover-display', 'none'))

        // Set markerpoint to draggable depending on if user is marker admin and has set edit mode to true or not
        if (mkpoint.user_id === sessionId && this.mode == 'edit') marker.setDraggable(true)
        else if (mkpoint.user_id === sessionId && this.mode == 'default') marker.setDraggable(false)
    }

    updateMkpoints () {

        if (this.data.mkpoints && this.map.getZoom() > this.mkpointsZoomRoof) {

            const bounds = this.map.getBounds()
            const mkpoints = this.data.mkpoints

            // Sort mkpoints in popularity order
            mkpoints.sort((a, b) => a.popularity - b.popularity)

            // First, remove all mkpoints that have left bounds
            var collection = this.mkpointsMarkerCollection
            let i = 0
            while (i < collection.length) {
                // If existing marker is not inside new bounds OR should not be displayed at this zoom level
                if ((!(collection[i]._lngLat.lat < bounds._ne.lat && collection[i]._lngLat.lat > bounds._sw.lat) || !(collection[i]._lngLat.lng < bounds._ne.lng && collection[i]._lngLat.lng > bounds._sw.lng)) || !this.zoomPopularityFilter(collection[i].popularity)) {
                    // If existing mkpoint is not favoured
                    if (!collection[i].isFavorite) {
                        collection[i].remove() // Remove it from the DOM
                        collection.splice(i, 1) // Remove it from instance Nodelist
                        i--
                    }
                }
                i++
            }

            // Second, add all mkpoints that have entered bounds
            let mkpointsSet = collection.length
            let keepMkpoints = []
            let j = 0
            while (j < mkpoints.length && mkpointsSet <= this.mkpointsMaxNumber) {
                // If mkpoint is inside bounds
                if ((mkpoints[j].lat < bounds._ne.lat && mkpoints[j].lat > bounds._sw.lat) && (mkpoints[j].lng < bounds._ne.lng && mkpoints[j].lng > bounds._sw.lng)) {
                    
                    // Verify it has not already been loaded
                    if (!document.querySelector('#mkpoint' + mkpoints[j].id)) {
                        // Filter through zoom popularity algorithm
                        if (this.zoomPopularityFilter(mkpoints[j].popularity) == true) {
                            this.setMkpoint(mkpoints[j])
                            mkpointsSet++
                        } else keepMkpoints.push(mkpoints[j])
                    }
                }
                j++
            }

            // Third, if overall number of mkpoints is still less than mkpointsMinNumber, add other mkpoints inside bounds up to a total number of mkpointsMinNumber
            if (mkpointsSet < this.mkpointsMinNumber) {
                for (let mkpointsToSet = 0; mkpointsToSet < this.mkpointsMinNumber - mkpointsSet && mkpointsToSet < keepMkpoints.length; mkpointsToSet++) {
                    this.setMkpoint(keepMkpoints[mkpointsToSet])
                }
            }

            // Update mkpoints scale
            document.querySelectorAll('.mkpoint-icon').forEach((mkpointIcon) => this.scaleMarkerAccordingToZoom(mkpointIcon))

        } else {
            for (let i = 0; i < this.mkpointsMarkerCollection.length; i++) {
                if (!this.mkpointsMarkerCollection[i].isFavorite) {
                    this.mkpointsMarkerCollection[i].remove()
                    this.mkpointsMarkerCollection.splice(i, 1)
                    i--
                }
            }
        }
    }

    async getMkpointMarker (mkpoint) {
        return new Promise((resolve, reject) => {
            this.mkpointsMarkerCollection.forEach((marker) => {
                if (getIdFromString(marker.getElement().id) == parseInt(mkpoint.id)) resolve(marker)
            } )
        } )
    }

    addFavoriteMkpoints () {
        this.data.mkpoints.forEach( (mkpoint) => {
            // Verify it has not already been loaded
            if (!document.querySelector('#mkpoint' + mkpoint.id)) {
                if (mkpoint.isFavorite) this.setMkpoint(mkpoint)
            }
        } )
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
            
            const rides = this.data.rides

            // First, remove all rides that have left bounds
            let i = 0
            while (i < this.ridesCollection.length) {
                // If existing ride is not inside new bounds
                if (!CFUtils.lineCoordsInsideBounds(this.ridesCollection[i].route.coordinates, this.map.getBounds().toArray())) {
                    if (this.map.getLayer('ride' + this.ridesCollection[i].id)) this.hideRide(this.ridesCollection[i]) // Remove it from the map
                    this.ridesCollection.splice(i, 1) // Remove it from instance Nodelist
                    i--
                }
                i++
            }

            // Second, add all rides that have entered bounds
            if (rides) rides.forEach( (ride) => {
                // If ride is public and has a route data
                if (ride.privacy == 'Public' && ride.route) {
                    // If ride is inside bounds
                    if (CFUtils.lineCoordsInsideBounds(ride.route.coordinates, this.map.getBounds().toArray())) {
                        // Verify it has not already been loaded
                        if (!this.isLinestringAlreadyDisplayed(ride)) {
                            this.ridesCollection.push(ride)
                            this.displayRide(ride)
                        }
                    }
                }
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
                coordinates: ride.route.coordinates
            }
        }
        
        if (!this.map.getSource('ride' + ride.id) && !this.map.getLayer('ride' + ride.id)) {

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
    }

    clickOnRide = (e) => {

        // Don't open if there is a marker on top
        var markerInPath
        e.originalEvent.composedPath().forEach(elementInPath => {
            if (elementInPath.className && elementInPath.className.includes('mapboxgl-marker')) markerInPath = true
        } )
        if (!markerInPath) {
            
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
                ride.ridePopup.popup.setLngLat(ride.route.coordinates[0])

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
            if (document.querySelector('#rideFeaturedImage' + ride.id)) document.querySelector('#rideFeaturedImage' + ride.id).src = 'data:image/jpeg;base64,' + featuredCheckpoint.img
        } )

    }

    async updateSegments () {
        return new Promise((resolve, reject) => {

            // If current zoom is precise enough
            if (this.map.getZoom() > this.segmentsZoomRoof) {

                const segments = this.data.segments

                // First, remove all segments that have left bounds
                let i = 0
                while (i < this.segmentsCollection.length) {
                    // If existing segment is not inside new bounds, or if it is not displayable at this zoom level
                    if (!CFUtils.lineCoordsInsideBounds(this.segmentsCollection[i].coordinates, this.map.getBounds().toArray()) || !this.isSegmentToDisplay(this.segmentsCollection[i])) {
                        if (this.map.getLayer('segment' + this.segmentsCollection[i].id)) this.hideSegment(this.segmentsCollection[i]) // Remove it from the map
                        this.segmentsCollection.splice(i, 1) // Remove it from instance Nodelist
                        i--
                    }
                    i++
                }

                // Second, add all segments that have entered bounds
                if (segments) segments.forEach( (segment) => {
                    // If segment is public and has a route data
                    if (this.isSegmentToDisplay(segment)) {
                        // If segment is inside bounds
                        if (CFUtils.lineCoordsInsideBounds(segment.coordinates, this.map.getBounds().toArray())) {
                            // Verify it has not already been loaded
                            if (!this.isLinestringAlreadyDisplayed(segment)) {
                                this.segmentsCollection.push(segment)
                                this.displaySegment(segment)
                            }
                        }
                    }
                } )

            // If current zoom is not precise enough
            } else {
                // Hide all segments and clear instance property
                this.segmentsCollection.forEach( (segment) => {
                    if (this.map.getLayer('segment' + segment.id)) this.hideSegment(segment)
                } )
                this.segmentsCollection = []
            }
            resolve (true)
        } )
    }

    displaySegment (segment) {

        // Build geojson
        var geojson = {
            type: 'Feature',
            properties: {
                rank: segment.rank,
                name: segment.name,
                tags: [],
                tunnels: segment.tunnels
            },
            geometry: {
                type: 'LineString',
                coordinates: segment.coordinates
            }
        }
        segment.tags.forEach(tag => geojson.properties.tags.push(tag))

        // Add source
        this.map.addSource('segment' + segment.id, {
            type: 'geojson',
            lineMetrics: true,
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
        return new Promise (async (resolve, reject) => {

            // Don't open if there is a marker on top
            var markerInPath
            e.originalEvent.composedPath().forEach(elementInPath => {
                if (elementInPath.className && elementInPath.className.includes('mapboxgl-marker')) markerInPath = true
            } )
            if (!markerInPath) {

                // Don't open if there is another feature on top
                if (this.map.queryRenderedFeatures(e.point)[0].source.includes('segment')) {

                    // Get segment from segmentsCollection using layer ID
                    var segment
                    this.segmentsCollection.forEach(entry => {
                        if (entry.id == getIdFromString(e.features[0].source)) segment = entry
                    } )

                    var popup = await this.openSegmentPopup(segment)
                    resolve(popup)
                }
            }
        } )
    }

    openSegmentPopup (segment) {
        return new Promise (async (resolve, reject) => {
                
            // Create segment popup instance
            if (!segment.segmentPopup) {
                segment.mapInstance = this
                segment.segmentPopup = new SegmentPopup( {
                    closeOnClick: true,
                    anchor: 'bottom',
                    className: 'js-linestring marker-popup js-segment-popup'
                }, segment)
                // Prepare and display segment popup
                const popup = segment.segmentPopup.popup
                popup.setLngLat(segment.coordinates[0])
                popup.addTo(this.map)
                segment.segmentPopup.setFlyAlong = (flyAlongButton) => {
                    flyAlongButton.addEventListener('click', async () => {
                        this.map.off('moveend', this.updateMapDataListener)
                        await this.flyAlong(turf.lineString(segment.coordinates), {layerId: 'segment' + segment.id})
                        this.map.on('moveend', this.updateMapDataListener)
                    } )
                }
                
                // Hide segment cap when popup is closed
                popup.on('close', () => {
                    if (this.map.getLayer('segmentCap' + segment.id)) {
                        this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 0)
                        this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.segmentCapColor)
                    }
                } )
            } else {
                segment.segmentPopup.popup.setLngLat(segment.coordinates[0])
                segment.segmentPopup.popup.addTo(this.map)
            }

            // Color segment cap in hovering style
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.capColorHover)

            // Update segmentsCollection entry
            this.segmentsCollection.forEach((entry) => {
                if (segment.id == entry.id) entry = segment
            } )

            // Focus on segment and redraw profile to ensure full segment is covered
            this.focus(this.map.getSource('segment' + segment.id)._data).then(() => segment.segmentPopup.profile.generate({sourceName: 'segment' + segment.id}))

            resolve(segment.segmentPopup.popup)
        } )
    }

    hideSegment (segment) {
        if (this.map.getLayer('segment' + segment.id)) this.map.removeLayer('segment' + segment.id)
        if (this.map.getLayer('segmentCap' + segment.id)) this.map.removeLayer('segmentCap' + segment.id)
        if (this.map.getSource('segment' + segment.id)) this.map.removeSource('segment' + segment.id)
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

    addTempMarker (lngLat) {
        var marker = new mapboxgl.Marker(
            {
                color: '#fffa9c',
                draggable: true,
                scale: 0.8
            }
        )
        marker.elevation = Math.floor(this.map.queryTerrainElevation(lngLat))
        marker.setLngLat(lngLat)

        var tempPopup = new TempPopup()
        var popup = tempPopup.popup
        tempPopup.load()
        marker.setPopup(popup)
        popup.on('open', async () => {
            // Display a preview on photo upload
            var file = document.getElementById('file')
            var previewImage = document.querySelector('.mp-image-preview')
            file.addEventListener('change', (e) => {
                previewImage.src = URL.createObjectURL(e.target.files[0])
            } )
            // Save data on submit and display new data
            var mkpoint = await tempPopup.save()
            this.addMkpoint(mkpoint)
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

    onClickBound = this.onClick.bind(this)
    onClick (e) {
        var marker = this.addTempMarker(e.lngLat)
        marker.getElement().addEventListener('contextmenu', this.removeOnClick)
    }

    // Create a numeral marker that can be deleted on right click
    async editMode () {

        var editModeBox = document.querySelector('#editModeBox')
        var sessionId = await CFSession.get('id')

        // If box is checked
        if (editModeBox.checked) {
            this.map.on('click', this.onClickBound)
            this.mode = 'edit'
            // Enable removing temp marker on left click
            this.tempMarkerCollection.forEach((existingMarker) => {
                let $existingMarker = existingMarker.getElement()
                let popup = existingMarker.getPopup()
                popup.options.className = 'hidden' // Hide popup in edit mode
                $existingMarker.addEventListener('contextmenu', this.removeOnClick)
            } )
            // Disable opening popup on click on mkpoint markers
            this.mkpointsMarkerCollection.forEach((mkpoint) => mkpoint.getPopup().options.className = 'marker-popup, hidden')
            // Highlight mkpoints
            this.mkpointsMarkerCollection.forEach((mkpoint) => {
                if (mkpoint._popup.user_id == sessionId) mkpoint._element.firstChild.classList.add('admin-marker')
            } )
            // Change cursor style
            this.map.getCanvas().classList.add('edit-mode')
            // Enable dragging on temp markers
            this.tempMarkerCollection.forEach((marker) => marker.setDraggable(true))

        // If box is not checked
        } else {
            this.map.off('click', this.onClickBound)
            this.mode = 'default'
            // Disable removing temp marker on left click
            this.tempMarkerCollection.forEach((existingMarker) => {
                let $existingMarker = existingMarker.getElement()
                let popup = existingMarker.getPopup()
                popup.options.className = 'marker-popup' // Display popup outside edit mode
                popup.id = $existingMarker.id // Attributes an ID to popup
                popup.elevation = existingMarker.elevation // Pass elevation data to the popup
                $existingMarker.removeEventListener('contextmenu', this.removeOnClick)
            } )
            // Enable opening popup on click on mkpoint markers
            this.mkpointsMarkerCollection.forEach((mkpoint) => mkpoint.getPopup().options.className = 'marker-popup')
            // Remove highlighting from markers
            this.mkpointsMarkerCollection.forEach((mkpoint) =>  {
                if (mkpoint._popup.user_id == sessionId) mkpoint._element.firstChild.classList.remove('admin-marker')
            } )
            // Change cursor style
            this.map.getCanvas().classList.remove('edit-mode')
            // Disable dragging on temp markers
            this.tempMarkerCollection.forEach((marker) => marker.setDraggable(false))
        }
    }

    addMkpoint (mkpoint) {
        this.data.mkpoints.push(mkpoint)
        this.updateMkpoints()
    }

    // Highlighting connected user markers 
    async highlightMyMkpointsMode () {

        var highlightMyMkpointsBox = document.querySelector('#highlightMyMkpointsBox')
        var sessionId = await CFSession.get('id')

        if (highlightMyMkpointsBox.checked) {
            this.highlight = true
            document.querySelectorAll('.mkpoint-icon').forEach( ($icon) => {
                if ($icon.parentElement.dataset.user_id === this.session.id) {
                    $icon.classList.add('admin-marker')
                }
            } )
        } else {
            this.highlight = false
            document.querySelectorAll('.mkpoint-icon').forEach( ($icon) => {
                if ($icon.parentElement.dataset.user_id === this.session.id) {
                    $icon.classList.remove('admin-marker')
                }
            } )
        }
    }
}