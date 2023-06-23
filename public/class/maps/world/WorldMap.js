import CFUtils from "/class/utils/CFUtils.js"
import CFSession from "/class/utils/CFSession.js"
import WorldHelper from "/scripts/helpers/map.js"
import Map from "/class/maps/Map.js"
import RidePopup from "/class/maps/ride/RidePopup.js"
import SegmentPopup from "/class/maps/segment/SegmentPopup.js"
import TempPopup from "/class/maps/world/TempPopup.js"

export default class WorldMap extends Map {

    constructor (options) {
        super(options)
    }

    type = 'worldMap'
    data = {}
    cursor = 1
    tempMarkerCollection = []
    ridesCollection = []
    segmentsCollection = []
    displaySceneriesBox
    displayRidesBox
    displaySegmentsBox
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

    loadSceneries () {
        ajaxGetRequest (this.apiUrl + "?display-sceneries=details", (sceneries) => {
            this.mapdata.sceneries = sceneries
        } )
    }

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
        this.displaySceneriesBox = document.createElement('input')
        this.displaySceneriesBox.id = 'displaySceneriesBox'
        this.displaySceneriesBox.setAttribute('type', 'checkbox')
        this.displaySceneriesBox.setAttribute('checked', 'checked')
        line1.appendChild(this.displaySceneriesBox)
        var displaySceneriesBoxLabel = document.createElement('label')
        displaySceneriesBoxLabel.setAttribute('for', 'displaySceneriesBox')
        displaySceneriesBoxLabel.innerText = '絶景スポットを表示'
        line1.appendChild(displaySceneriesBoxLabel)
        this.displaySceneriesBox.addEventListener('change', () => {
            if (this.displaySceneriesBox.checked) this.updateSceneries()
            else this.hideSceneries()
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
        this.displayActivityPhotosBox = document.createElement('input')
        this.displayActivityPhotosBox.id = 'displayActivityPhotosBox'
        this.displayActivityPhotosBox.setAttribute('type', 'checkbox')
        this.displayActivityPhotosBox.setAttribute('checked', 'true')
        line4.appendChild(this.displayActivityPhotosBox)
        this.displayActivityPhotosBox.addEventListener('click', () => {
            if (this.displayActivityPhotosBox.checked) this.updateActivityPhotos()
            else this.activityPhotosMarkerCollection.forEach( (marker) => marker.remove())
            this.activityPhotosMarkerCollection = []
        } )
        var displayActivityPhotosBoxLabel = document.createElement('label')
        displayActivityPhotosBoxLabel.setAttribute('for', 'displayActivityPhotosBox')
        displayActivityPhotosBoxLabel.innerText = 'アクティビティ写真を表示'
        line4.appendChild(displayActivityPhotosBoxLabel)
        // Line 5
        let line5 = document.createElement('div')
        line5.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line5)
        this.dislayKonbinisBox = document.createElement('input')
        this.dislayKonbinisBox.id = 'dislayKonbinisBox'
        this.dislayKonbinisBox.setAttribute('type', 'checkbox')
        this.dislayKonbinisBox.setAttribute('checked', 'true')
        line5.appendChild(this.dislayKonbinisBox)
        this.dislayKonbinisBox.addEventListener('click', () => {
            if (this.dislayKonbinisBox.checked) this.addKonbiniLayers()
            else this.hideKonbiniLayers()
        } )
        var dislayKonbinisBoxLabel = document.createElement('label')
        dislayKonbinisBoxLabel.setAttribute('for', 'dislayKonbinisBox')
        dislayKonbinisBoxLabel.innerText = 'コンビニを表示'
        line5.appendChild(dislayKonbinisBoxLabel)
        // Line 6
        let line6 = document.createElement('div')
        line6.className = 'map-controller-line hide-on-mobiles'
        optionsContainer.appendChild(line6)
        this.displayAmenitiesBox = document.createElement('input')
        this.displayAmenitiesBox.id = 'displayAmenitiesBox'
        this.displayAmenitiesBox.setAttribute('type', 'checkbox')
        this.displayAmenitiesBox.setAttribute('checked', 'true')
        line6.appendChild(this.displayAmenitiesBox)
        this.displayAmenitiesBox.addEventListener('click', () => {
            if (this.displayAmenitiesBox.checked) this.addAmenityLayers()
            else this.hideAmenityLayers()
        } )
        var displayAmenitiesBoxLabel = document.createElement('label')
        displayAmenitiesBoxLabel.setAttribute('for', 'displayAmenitiesBox')
        displayAmenitiesBoxLabel.innerText = 'アメニティを表示'
        line6.appendChild(displayAmenitiesBoxLabel)
        
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
            this.reloadSegments()
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
            if (editModeBox.checked) await WorldHelper.onEditSceneriesStart()
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
        var highlightMySceneriesBox = document.createElement('input')
        highlightMySceneriesBox.id = 'highlightMySceneriesBox'
        highlightMySceneriesBox.setAttribute('type', 'checkbox')
        line2.appendChild(highlightMySceneriesBox)
        highlightMySceneriesBox.addEventListener('click', () => {
            this.highlightMySceneriesMode()
        } ) // Data treatment
        var highlightMySceneriesBoxLabel = document.createElement('label')
        highlightMySceneriesBoxLabel.setAttribute('for', 'highlightMySceneriesBox')
        highlightMySceneriesBoxLabel.innerText = '自分の絶景スポットを表示'
        line2.appendChild(highlightMySceneriesBoxLabel)
        
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

    async getSceneryMarker (scenery) {
        return new Promise((resolve, reject) => {
            this.sceneriesMarkerCollection.forEach((marker) => {
                if (getIdFromString(marker.getElement().id) == parseInt(scenery.id)) resolve(marker)
            } )
        } )
    }
    
    updateMapData () {
        if (!this.displaySceneriesBox || this.displaySceneriesBox.checked) this.updateSceneries()
        if (!this.displayRidesBox || this.displayRidesBox.checked) this.updateRides()
        if (!this.displaySegmentsBox || this.displaySegmentsBox.checked) this.updateSegments()
        if (!this.displayActivityPhotos || this.displayActivityPhotos.checked) this.updateActivityPhotos()
    }

    updateRides () {

        // If current zoom is precise enough
        if (this.map.getZoom() > this.ridesZoomRoof) {
            
            const rides = this.mapdata.rides

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
                if (ride.privacy == 'public' && ride.route) {
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
            if (document.querySelector('#rideFeaturedImage' + ride.id)) document.querySelector('#rideFeaturedImage' + ride.id).src = featuredCheckpoint.url
        } )

    }

    async updateSegments () {
        return new Promise((resolve, reject) => {

            // If current zoom is precise enough
            if (this.map.getZoom() > this.segmentsZoomRoof) {

                const segments = this.mapdata.segments

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

    /**
     * Relauch a segments update from scratch
     */
    reloadSegments () {
        // Hide all segments and clear instance property
        this.segmentsCollection.forEach( (segment) => {
            if (this.map.getLayer('segment' + segment.id)) this.hideSegment(segment)
        } )
        this.segmentsCollection = []
        // Reupdate segments
        this.updateSegments()
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
        else if (segment.rank == 'regional') var segmentColor = this.segmentRegionalColor
        else if (segment.rank == 'national') var segmentColor = this.segmentNationalColor

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

        // If current month corresponds to advised season, add segment season cap layer
        var isAdvisedSeason = false
        if (segment.seasons) segment.seasons.forEach(season => {
            if (CFUtils.monthInsidePeriod(this.month, [season['period_start_month'], season['period_end_month']])) isAdvisedSeason = true
        })
        if (isAdvisedSeason)this.map.addLayer( {
            id: 'segmentSeasonCap' + segment.id,
            type: 'line',
            source: 'segment' + segment.id,
            layout: {
                'line-join': 'round',
                'line-cap': 'round'
            },
            paint: {
                'line-color': this.segmentSeasonColor,
                'line-width': 2,
                'line-opacity': 0.5,
                'line-gap-width': 2
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
        if (this.map.getLayer('segmentSeasonCap' + segment.id)) this.map.removeLayer('segmentSeasonCap' + segment.id)
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
            var scenery = await tempPopup.save()
            this.addScenery(scenery)
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
            // Disable opening popup on click on scenery markers
            this.sceneriesMarkerCollection.forEach((scenery) => scenery.getPopup().options.className = 'marker-popup, hidden')
            // Highlight sceneries
            this.sceneriesMarkerCollection.forEach((scenery) => {
                if (scenery._popup.user_id == sessionId) scenery._element.firstChild.classList.add('admin-marker')
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
            // Enable opening popup on click on scenery markers
            this.sceneriesMarkerCollection.forEach((scenery) => scenery.getPopup().options.className = 'marker-popup')
            // Remove highlighting from markers
            this.sceneriesMarkerCollection.forEach((scenery) =>  {
                if (scenery._popup.user_id == sessionId) scenery._element.firstChild.classList.remove('admin-marker')
            } )
            // Change cursor style
            this.map.getCanvas().classList.remove('edit-mode')
            // Disable dragging on temp markers
            this.tempMarkerCollection.forEach((marker) => marker.setDraggable(false))
        }
    }

    addScenery (scenery) {
        this.mapdata.sceneries.push(scenery)
        this.updateSceneries()
    }

    // Highlighting connected user markers 
    async highlightMySceneriesMode () {

        var highlightMySceneriesBox = document.querySelector('#highlightMySceneriesBox')
        var sessionId = await CFSession.get('id')

        if (highlightMySceneriesBox.checked) {
            this.highlight = true
            document.querySelectorAll('.scenery-icon').forEach( ($icon) => {
                if ($icon.parentElement.dataset.user_id === sessionId) {
                    $icon.classList.add('admin-marker')
                }
            } )
        } else {
            this.highlight = false
            document.querySelectorAll('.scenery-icon').forEach( ($icon) => {
                if ($icon.parentElement.dataset.user_id === sessionId) {
                    $icon.classList.remove('admin-marker')
                }
            } )
        }
    }
}