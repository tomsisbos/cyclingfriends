import CFUtils from "/map/class/CFUtils.js"
import CFSession from "/map/class/CFSession.js"
import WorldMap from "/map/class/WorldMap.js"
import AmenityPopup from "/map/class/AmenityPopup.js"

var worldMap = new WorldMap()

// Set default layer according to current season
var map = await worldMap.load(document.querySelector('#worldMap'), 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')

// Load CF sources and layers
worldMap.addSources()
worldMap.addLayers()

/* -- Controls -- */

worldMap.addStyleControl()
worldMap.addOptionsControl()
worldMap.addFilterControl()
worldMap.addFullscreenControl()
CFSession.hasRights('editor').then((response) => {
    if (response === true) worldMap.addEditorControl()
} )

// Prepare and display mkpoints data
ajaxGetRequest (worldMap.apiUrl + "?display-mkpoints=details", (mkpoints) => {
    worldMap.data.mkpoints = mkpoints
    if (worldMap.displayMkpointsBox.checked) {
        worldMap.updateMkpoints()
        worldMap.addFavoriteMkpoints()
    }
} )

// Prepare and display segments data
ajaxGetRequest (worldMap.apiUrl + "?display-segments=true", (segments) => {
    worldMap.data.segments = segments
    if (worldMap.displaySegmentsBox.checked) worldMap.updateSegments()
} )

// Prepare and display rides data
ajaxGetRequest (worldMap.apiUrl + "?display-rides=true", (rides) => {
    worldMap.data.rides = rides
    if (worldMap.displayRidesBox.checked) worldMap.updateRides()
} )

// Update map data on ending moving the map
map.on('moveend', worldMap.updateMapDataListener)

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
        if (amenity == 'cycle-path' && feature.properties.name != '') map.setFilter('cycle-path-cap', ['in', 'name', feature.properties.name]) // Display cycling road cap on cycling road hovering
        else map.setFilter('cycle-path-cap', ['in', 'id', feature.properties.id])

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
            text += '<strong>路面 : </strong>'
            if (amenityPopup.data.surface) text += CFUtils.getSurfaceFromvalue(amenityPopup.data.surface)
            else if (amenityPopup.data.tracktype) switch (amenityPopup.data.tracktype) {
                case 'grade1': text += '舗装／グラベル'; break
                case 'grade2': text += 'グラベル'; break
                case 'grade3': text += 'グラベル／ダート'; break
                case 'grade4': text += 'ダート'; break
                case 'grade5': text += '走行不能'; break
            }
            else text += 'データ無し'
            // Set width
            text += '<br><strong>幅員 : </strong>'
            let width
            if (amenityPopup.data.width) width = amenityPopup.data.width
            else if (amenityPopup.data['yh:WIDTH']) width = amenityPopup.data['yh:WIDTH']
            else width = 'データ無し'
            if (width != 'データ無し' && !width.includes('m')) width += 'm'
            text += width
            // Set permission
            if (amenityPopup.data.bicycle == 'no') '<br>(!) 自転車はこの道を通行出来ません。'
            else if (amenityPopup.data.access == 'permissive' || amenityPopup.data.bicycle == 'permissive') text += '<br>この道は許可車両のみ通行出来ます。'
            else if (amenityPopup.data.access == 'no') text += '<br>(!) 自転車はこの道を通行出来ません。'
            else if (amenityPopup.data.tracktype) switch (amenityPopup.data.tracktype) {
                case 'grade4': text += '<br>自転車はこの道を通行出来ません。'; break
                case 'grade5': text += '<br>自転車はこの道を通行出来ません。'; break
            }
            else if (amenityPopup.data.highway == 'path') text += '<br>自転車はこの道を通行出来ません。'

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
        map.getCanvas().style.cursor = 'unset'

        // Remove popup
        amenityPopup.popup.remove()
    } )
} )