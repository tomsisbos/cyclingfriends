import CFUtils from "/map/class/CFUtils.js"
import MapMap from "/map/class/MapMap.js"
import AmenityPopup from "/map/class/AmenityPopup.js"

var mapMap = new MapMap()
console.log(mapMap)

// Set default layer according to current season
var map = await mapMap.load(document.querySelector('#mapMap'), 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

// Load CF sources and layers
mapMap.addSources()
mapMap.addLayers()

console.log(mapMap.session)

/* -- Controls -- */

// Controller
var controller = document.createElement('div')
controller.className = 'map-controller'
mapMap.$map.after(controller)

// Map style
var selectStyleContainer = document.createElement('div')
selectStyleContainer.className = 'map-controller-block bold'
controller.appendChild(selectStyleContainer)
var selectStyleLabel = document.createElement('div')
selectStyleLabel.innerText = 'Map style : '
selectStyleContainer.appendChild(selectStyleLabel)
var selectStyle = document.createElement('select')
var seasons = document.createElement("option")
var satellite = document.createElement("option")
seasons.value, seasons.text = 'Seasons'
seasons.setAttribute('selected', 'selected')
seasons.id = 'cl07xga7c002616qcbxymnn5z'
satellite.id = 'cl0chu1or003a15nocgiodiir'
satellite.value, satellite.text = 'Satellite'
selectStyle.add(seasons)
selectStyle.add(satellite)
selectStyle.className = 'js-map-styles'
selectStyleContainer.appendChild(selectStyle)

// Main controller
var mainContainer = document.createElement('div')
mainContainer.className = 'map-controller map-inner-controller'
controller.appendChild(mainContainer)

// Map options
var optionsContainer = document.createElement('div')
optionsContainer.className = 'map-controller-block flex-column'
mainContainer.appendChild(optionsContainer)
// Label
var optionsLabel = document.createElement('div')
optionsLabel.innerText = 'Options'
optionsLabel.className = 'map-controller-label'
optionsContainer.appendChild(optionsLabel)
// Line 1
let line1 = document.createElement('div')
line1.className = 'map-controller-line'
optionsContainer.appendChild(line1)
mapMap.displayMkpointsBox = document.createElement('input')
mapMap.displayMkpointsBox.id = 'displayMkpointsBox'
mapMap.displayMkpointsBox.setAttribute('type', 'checkbox')
mapMap.displayMkpointsBox.setAttribute('checked', 'checked')
line1.appendChild(mapMap.displayMkpointsBox)
var displayMkpointsBoxLabel = document.createElement('label')
displayMkpointsBoxLabel.setAttribute('for', 'displayMkpointsBox')
displayMkpointsBoxLabel.innerText = 'Show scenery points'
line1.appendChild(displayMkpointsBoxLabel)
mapMap.displayMkpointsBox.addEventListener('change', () => {
    if (mapMap.displayMkpointsBox.checked) mapMap.updateMkpoints()
    else mapMap.hideMkpoints()
} )
// Line 2
let line2 = document.createElement('div')
line2.className = 'map-controller-line'
optionsContainer.appendChild(line2)
mapMap.displayRidesBox = document.createElement('input')
mapMap.displayRidesBox.id = 'displayRidesBox'
mapMap.displayRidesBox.setAttribute('type', 'checkbox')
mapMap.displayRidesBox.setAttribute('checked', 'true')
line2.appendChild(mapMap.displayRidesBox)
mapMap.displayRidesBox.addEventListener('click', () => {
    if (mapMap.displayRidesBox.checked) mapMap.updateRides()
    else mapMap.ridesCollection.forEach( (ride) => {
        if (map.getLayer('ride' + ride.id)) mapMap.hideRide(ride)
    } )
    mapMap.ridesCollection = []
} )
var displayRidesBoxLabel = document.createElement('label')
displayRidesBoxLabel.setAttribute('for', 'displayRidesBox')
displayRidesBoxLabel.innerText = 'Display rides'
line2.appendChild(displayRidesBoxLabel)
// Line 3
let line3 = document.createElement('div')
line3.className = 'map-controller-line'
optionsContainer.appendChild(line3)
mapMap.displaySegmentsBox = document.createElement('input')
mapMap.displaySegmentsBox.id = 'displaySegmentsBox'
mapMap.displaySegmentsBox.setAttribute('type', 'checkbox')
mapMap.displaySegmentsBox.setAttribute('checked', 'true')
line3.appendChild(mapMap.displaySegmentsBox)
mapMap.displaySegmentsBox.addEventListener('click', () => {
    if (mapMap.displaySegmentsBox.checked) mapMap.updateSegments()
    else mapMap.segmentsCollection.forEach( (segment) => {
        if (map.getLayer('segment' + segment.id)) mapMap.hideSegment(segment)
    } )
    mapMap.segmentsCollection = []
} )
var displaySegmentsBoxLabel = document.createElement('label')
displaySegmentsBoxLabel.setAttribute('for', 'displaySegmentsBox')
displaySegmentsBoxLabel.innerText = 'Display segments'
line3.appendChild(displaySegmentsBoxLabel)
// Line 4
let line4 = document.createElement('div')
line4.className = 'map-controller-line'
optionsContainer.appendChild(line4)
mapMap.dislayKonbinisBox = document.createElement('input')
mapMap.dislayKonbinisBox.id = 'dislayKonbinisBox'
mapMap.dislayKonbinisBox.setAttribute('type', 'checkbox')
mapMap.dislayKonbinisBox.setAttribute('checked', 'true')
line4.appendChild(mapMap.dislayKonbinisBox)
mapMap.dislayKonbinisBox.addEventListener('click', () => {
    if (mapMap.dislayKonbinisBox.checked) mapMap.addKonbiniLayers()
    else mapMap.hideKonbiniLayers()
} )
var dislayKonbinisBoxLabel = document.createElement('label')
dislayKonbinisBoxLabel.setAttribute('for', 'dislayKonbinisBox')
dislayKonbinisBoxLabel.innerText = 'Display konbinis'
line4.appendChild(dislayKonbinisBoxLabel)
// Line 5
let line5 = document.createElement('div')
line5.className = 'map-controller-line'
optionsContainer.appendChild(line5)
mapMap.displayAmenitiesBox = document.createElement('input')
mapMap.displayAmenitiesBox.id = 'dislayKonbinisBox'
mapMap.displayAmenitiesBox.setAttribute('type', 'checkbox')
mapMap.displayAmenitiesBox.setAttribute('checked', 'true')
line5.appendChild(mapMap.displayAmenitiesBox)
mapMap.displayAmenitiesBox.addEventListener('click', () => {
    if (mapMap.displayAmenitiesBox.checked) mapMap.addAmenityLayers()
    else mapMap.hideAmenityLayers()
} )
var displayAmenitiesBoxLabel = document.createElement('label')
displayAmenitiesBoxLabel.setAttribute('for', 'displayAmenitiesBox')
displayAmenitiesBoxLabel.innerText = 'Display amenities'
line5.appendChild(displayAmenitiesBoxLabel)

// Map editor
if (mapMap.session.rights === 'administrator' || mapMap.session.rights === 'editor') {
    // Container
    var editorContainer = document.createElement('div')
    editorContainer.className = 'map-controller-block flex-column bg-admin'
    mainContainer.appendChild(editorContainer)
    // Label
    var editorLabel = document.createElement('div')
    editorLabel.innerText = 'Editor'
    editorLabel.className = 'map-controller-label'
    editorContainer.appendChild(editorLabel)
    // Line 1
    let line1 = document.createElement('div')
    line1.className = 'map-controller-line'
    editorContainer.appendChild(line1)
    var editModeBox = document.createElement('input')
    editModeBox.id = 'editModeBox'
    editModeBox.setAttribute('type', 'checkbox')
    line1.appendChild(editModeBox)
    editModeBox.addEventListener('click', editMode) // Data treatment
    var editModeBoxLabel = document.createElement('label')
    editModeBoxLabel.setAttribute('for', 'editModeBox')
    editModeBoxLabel.innerText = 'Edit markers'
    line1.appendChild(editModeBoxLabel)
    // Line 2
    let line2 = document.createElement('div')
    line2.className = 'map-controller-line'
    editorContainer.appendChild(line2)
    var highlightMyMkpointsBox = document.createElement('input')
    highlightMyMkpointsBox.id = 'highlightMyMkpointsBox'
    highlightMyMkpointsBox.setAttribute('type', 'checkbox')
    line2.appendChild(highlightMyMkpointsBox)
    highlightMyMkpointsBox.addEventListener('click', highlightMyMkpointsMode) // Data treatment
    var highlightMyMkpointsBoxLabel = document.createElement('label')
    highlightMyMkpointsBoxLabel.setAttribute('for', 'highlightMyMkpointsBox')
    highlightMyMkpointsBoxLabel.innerText = 'Highlight my markers'
    line2.appendChild(highlightMyMkpointsBoxLabel)
}

// Controls
const mapStyleSelect = document.querySelector('.js-map-styles')
mapStyleSelect.onchange = (e) => {
    var index = e.target.selectedIndex
    var layerId = e.target.options[index].id
    if (layerId === 'seasons') layerId = mapMap.season
    mapMap.clearMapData()
    mapMap.setMapStyle(layerId)
    map.once('idle', () => mapMap.updateMapData())
}


/* -- Controller -- */

// Create a numeral marker that can be deleted on right click
function editMode () {

    // If box is checked
    if (editModeBox.checked) {
        map.on('click', addNewTempMarker)
        mapMap.mode = 'edit'
        // Enable removing temp marker on left click
        mapMap.tempMarkerCollection.forEach((existingMarker) => {
            let $existingMarker = existingMarker.getElement()
            let popup = existingMarker.getPopup()
            popup.options.className = 'hidden' // Hide popup in edit mode
            $existingMarker.addEventListener('contextmenu', mapMap.removeOnClick)
        } )
        // Disable opening popup on click on mkpoint markers
        mapMap.mkpointsMarkerCollection.forEach((mkpoint) => mkpoint.getPopup().options.className = 'marker-popup, hidden')
        // Highlight mkpoints
        mapMap.mkpointsMarkerCollection.forEach((mkpoint) => {
            if (mkpoint._popup.user_id == mapMap.session.id) mkpoint._element.firstChild.classList.add('admin-marker')
        } )
        // Change cursor style
        map.getCanvas().style.cursor = 'crosshair'
        // Enable dragging on temp markers
        mapMap.tempMarkerCollection.forEach((marker) => marker.setDraggable(true))
        console.log('editModeMarker has been enabled.')

    // If box is not checked
    } else {
        map.off('click', addNewTempMarker)
        mapMap.mode = 'default'
        // Disable removing temp marker on left click
        mapMap.tempMarkerCollection.forEach((existingMarker) => {
            let $existingMarker = existingMarker.getElement()
            let popup = existingMarker.getPopup()
            popup.options.className = 'marker-popup' // Display popup outside edit mode
            popup.id = $existingMarker.id // Attributes an ID to popup
            popup.elevation = existingMarker.elevation // Pass elevation data to the popup
            $existingMarker.removeEventListener('contextmenu', mapMap.removeOnClick)
        } )
        // Enable opening popup on click on mkpoint markers
        mapMap.mkpointsMarkerCollection.forEach((mkpoint) => mkpoint.getPopup().options.className = 'marker-popup')
        // Remove highlighting from markers
        mapMap.mkpointsMarkerCollection.forEach((mkpoint) =>  {
            if (mkpoint._popup.user_id == mapMap.session.id) mkpoint._element.firstChild.classList.remove('admin-marker')
        } )
        // Change cursor style
        map.getCanvas().style.cursor = 'grab'
        // Disable dragging on temp markers
        mapMap.tempMarkerCollection.forEach((marker) => marker.setDraggable(false))
        console.log('editModeMarker has been disabled.')
    }
}

// On edit mode, user can add markers by clicking on the map and removing them by right clicking on the marker
function addNewTempMarker (e) {
    var elevation = Math.floor(map.queryTerrainElevation(e.lngLat))
    var marker = mapMap.addTempMarker(e.lngLat, elevation)
    marker.getElement().addEventListener('contextmenu', mapMap.removeOnClick)
}

// Highlighting connected user markers 
function highlightMyMkpointsMode () {
    if (highlightMyMkpointsBox.checked) {
        mapMap.highlight = true
        document.querySelectorAll('.mkpoint-icon').forEach( ($icon) => {
            if ($icon.parentElement.dataset.user_id === mapMap.session.id) {
                $icon.classList.add('admin-marker')
            }
        } )
    } else {
        mapMap.highlight = false
        if (mapMap.mode != 'edit') {
            document.querySelectorAll('.mkpoint-icon').forEach( ($icon) => {
                if ($icon.parentElement.dataset.user_id === mapMap.session.id) {
                    $icon.classList.remove('admin-marker')
                }
            } )
        }
    }
}

/* -- Mkpoints -- */

// Displaying all mkpoints from map_mkpoints table inside the map
ajaxGetRequest (mapMap.apiUrl + "?display-mkpoints=true", (mkpoints) => {

    // Display mkpoints on first loading of the map
    if (map.getZoom() > mapMap.mkpointsZoomRoof) {
        map.once('idle', () => {
            mapMap.mkpointsMarkerCollection = []
            const bounds = map.getBounds ()
            mkpoints.forEach (async (mkpoint) => {
                // If mkpoint is inside bounds
                if ((mkpoint.lat < bounds._ne.lat && mkpoint.lat > bounds._sw.lat) && (mkpoint.lng < bounds._ne.lng && mkpoint.lng > bounds._sw.lng)) {
                    // Filter through zoom popularity algorithm
                    if (mapMap.zoomPopularityFilter(mkpoint.popularity) == true) {
                        mapMap.setMkpoint(mkpoint)
                    }
                }
            } )
        } )
    }

    // Display rides and segments on first loading of the map
    if (map.getZoom() > mapMap.ridesZoomRoof) {
        mapMap.updateRides()
        mapMap.updateSegments()
    }

    // Update mkpoints, rides and segments display on ending moving the map
    map.on('moveend', mapMap.updateMapDataListener)
} )

var amenities = ['toilets', 'drinking-water', 'vending-machine-drinks', 'seven-eleven', 'family-mart', 'mb-family-mart', 'lawson', 'mini-stop', 'daily-yamazaki', 'michi-no-eki', 'onsens', 'footbaths', 'rindos-case', 'cycle-path']
amenities.forEach( (amenity) => {
    let hoveredFeatureId = null
    let feature
    var amenityPopup = new AmenityPopup()

    map.on('mouseenter', amenity, (e) => {

        // Get amenity properties
        map.queryRenderedFeatures(e.point).forEach( (thisFeature) => {
            if (thisFeature.layer.id == amenity) {
                feature = thisFeature
                console.log(feature)
                amenityPopup.data = feature.properties
            }
        } )

        // Add hover style to this feature
        map.getCanvas().style.cursor = 'pointer'
        // Set hover state to feature
        if (hoveredFeatureId !== null) map.setFeatureState({source: feature.source, id: hoveredFeatureId}, {hover: false})
        hoveredFeatureId = feature.id
        var state = {source: feature.source, id: hoveredFeatureId}
        if (feature.sourceLayer) state.sourceLayer = feature.sourceLayer
        map.setFeatureState(state, {hover: true})
        if (!feature.properties.name) feature.properties.name = ''
        if (amenity == 'rindos-case') map.setFilter('rindos-cap', ['in', 'name', feature.properties.name]) // Display rindo cap on rindo hovering
        if (amenity == 'cycle-path') map.setFilter('cycle-path-cap', ['in', 'name', feature.properties.name]) // Display rindo cap on rindo hovering

        // Define popup content
        if (amenity == 'onsens' || amenity == 'michi-no-eki') {
            if (amenityPopup.data.name.includes(';')) var text = amenityPopup.data.name.replace(';', '<br>')
            else var text = amenityPopup.data.name

        } else if (amenity == 'seven-eleven' || amenity == 'family-mart' || amenity == 'mb-family-mart' || amenity == 'lawson' || amenity == 'mini-stop' || amenity == 'daily-yamazaki') {
            if (amenityPopup.data.branch) var text = CFUtils.getAmenityName(amenity) + '<br>' + amenityPopup.data.branch
            else if (amenityPopup.data.name.includes('店')) var text = CFUtils.getAmenityName(amenity) + '<br>' + amenityPopup.data.name
            else text = CFUtils.getAmenityName(amenity)
            if (text.includes('<br>') && !text.includes('店')) text += '店'

        } else if (amenity == 'rindos-case') {
            // Build title
            var text = '<div class="pb-2"><div class="popup-properties-name">' + amenityPopup.data.name + '</div>'
            if (amenityPopup.data['name:en']) text += amenityPopup.data['name:en']
            text += '</div>'
            // Set surface
            text += '<strong>Surface : </strong>'
            if (amenityPopup.data.surface) text += CFUtils.getSurfaceFromvalue(amenityPopup.data.surface)
            else if (amenityPopup.data.tracktype) switch (amenityPopup.data.tracktype) {
                case 'grade1': text += 'Asphalt, Gravel'; break
                case 'grade2': text += 'Gravel'; break
                case 'grade3': text += 'Gravel, Dirt'; break
                case 'grade4': text += 'Dirt'; break
                case 'grade5': text += 'Unrideable'; break
            }
            else text += 'No data'
            // Set width
            text += '<br><strong>Width : </strong>'
            let width
            if (amenityPopup.data.width) width = amenityPopup.data.width
            else if (amenityPopup.data['yh:WIDTH']) width = amenityPopup.data['yh:WIDTH']
            else width = 'No data'
            if (width != 'No data' && !width.includes('m')) width += 'm'
            text += width
            // Set permission
            if (amenityPopup.data.bicycle == 'no') '<br>(!) Bicycles are not allowed on this road.'
            else if (amenityPopup.data.access == 'permissive' || amenityPopup.data.bicycle == 'permissive') text += '<br>This road is accessible by bicycle unless owner revoke permission.'
            else if (amenityPopup.data.access == 'no') text += '<br>(!) Bicycles are not allowed on this road.'
            else if (amenityPopup.data.tracktype) switch (amenityPopup.data.tracktype) {
                case 'grade4': text += '<br>This road is not accessible by bicycle.'; break
                case 'grade5': text += '<br>This road is not accessible by bicycle.'; break
            }
            else if (amenityPopup.data.highway == 'path') text += '<br>This road is not accessible by bicycle.'

        } else if (amenity == 'cycle-path') {
            // Build title
            if (amenityPopup.data.name) var text = '<div class="pb-2"><div class="popup-properties-name">' + amenityPopup.data.name + '</div>'
            else var text = '<div class="pb-2"><div class="popup-properties-name">' + CFUtils.getAmenityName(amenity) + '</div>'
            if (amenityPopup.data['name:en']) text += amenityPopup.data['name:en']
            text += '</div>'

        } else var text = CFUtils.getAmenityName(amenity)
        amenityPopup.popup.setHTML(text)

        // Add to map
        amenityPopup.popup.setLngLat(e.lngLat)
        amenityPopup.popup.addTo(map)
        console.log(map.getZoom())
    } )

    map.on('mouseout', amenity, (e) => {

        // Get amenity properties
        map.queryRenderedFeatures(e.point).forEach( (thisFeature) => {
            if (thisFeature.layer.id == amenity) {
                feature = thisFeature
                amenityPopup.data = feature.properties
            }
        } )

        // Remove hover style from this feature
        if (amenity == 'rindos-case') map.setFilter('rindos-cap', ['in', 'name', 'default'])
        if (amenity == 'cycle-path') map.setFilter('cycle-path-cap', ['in', 'name', 'default'])
        if (hoveredFeatureId !== null) {
            var state = {source: feature.source, id: hoveredFeatureId}
            if (feature.sourceLayer) state.sourceLayer = feature.sourceLayer
            map.setFeatureState(state, {hover: false})
        }
        hoveredFeatureId = null
        map.getCanvas().style.cursor = 'grab'

        // Remove popup
        amenityPopup.popup.remove()
    } )
} )