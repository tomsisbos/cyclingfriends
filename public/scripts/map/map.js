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

mapMap.addStyleControl()
mapMap.addOptionsControl()
mapMap.addFullscreenControl()

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

// Prepare and display mkpoints data
ajaxGetRequest (mapMap.apiUrl + "?display-mkpoints=true", (mkpoints) => {
    mapMap.data.mkpoints = mkpoints
    mapMap.updateMkpoints()
    mapMap.addFavoriteMkpoints()
} )

// Prepare and display segments data
ajaxGetRequest (mapMap.apiUrl + "?display-segments=true", (segments) => {
    mapMap.data.segments = segments
    mapMap.updateSegments()
} )

// Prepare and display rides data
ajaxGetRequest (mapMap.apiUrl + "?display-rides=true", (rides) => {
    mapMap.data.rides = rides
    mapMap.updateRides()
} )

// Update map data on ending moving the map
map.on('moveend', () => mapMap.updateMapData() )

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
        map.getCanvas().style.cursor = 'unset'

        // Remove popup
        amenityPopup.popup.remove()
    } )
} )