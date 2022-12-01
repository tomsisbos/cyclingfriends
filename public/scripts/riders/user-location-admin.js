import PickMap from "../../map/class/pickMap"

var pickMap = new PickMap()
const originalPosition = pickMap.currentPosition

const button = document.querySelector('#userLocationButton')

button.addEventListener('click', async () => {  
    
    // Open map window
    var element = openMapWindow()

    // Setup map
    var map = await pickMap.load(element, pickMap.defaultStyle)
    pickMap.addSources()
    pickMap.addLayers()

    // Display marker on current user address
    const marker = new mapboxgl.Marker( {
        draggable: true
    } )
    marker.setLngLat(pickMap.currentPosition)
    marker.addTo(map)

    // Update current position property to the marker new position
    marker.on('dragend', async (e) => pickMap.currentPosition = e.target._lngLat)

} )

function openMapWindow () {
    var modal = document.createElement('div')
    modal.classList.add('modal', 'd-block')
    document.querySelector('body').appendChild(modal)
    // Close modal on click outside popup
    modal.addEventListener('click', (e) => {
        // Close popup on click outside modal window
        var eTarget = e ? e.target : event.srcElement
        if ((eTarget != mapWindow && eTarget != mapWindow.firstElementChild) && (eTarget === modal)) closePopup(modal)
    } )
    var mapWindow = document.createElement('div')
    mapWindow.classList.add('pf-map-popup')
    var $pickMap = document.createElement('div')
    $pickMap.id = 'pickMap'
    mapWindow.appendChild($pickMap)
    modal.appendChild(mapWindow)
    return $pickMap
}

async function closePopup (modal) {

    // Close modal window
    modal.remove()

    // If location has changed, send new location data to the server
    if (pickMap.currentPosition != originalPosition) {

        // Get geolocation data of this point from map provider
        console.log(pickMap.currentPosition)
        var geolocation = await pickMap.getCourseGeolocation(pickMap.currentPosition)

        // Build variable to send to server
        var userLocationData = {
            lngLat: pickMap.currentPosition,
            geolocation
        }

        // Send data to server and get user location data sorted
        ajaxJsonPostRequest (pickMap.apiUrl, userLocationData, async (message) => {
            showResponseMessage(message)
        } )

    }
}