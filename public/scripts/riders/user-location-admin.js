import PickMap from "/class/maps/user/PickMap.js"

var pickMap = new PickMap()
const originalPosition = await pickMap.getUserLocation()

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
    if (pickMap.currentPosition) marker.setLngLat(pickMap.currentPosition)
    else marker.setLngLat(await pickMap.getUserLocation())
    marker.addTo(map)

    // Update current position property to the marker new position
    marker.on('dragend', (e) => pickMap.currentPosition = [e.target._lngLat.lng, e.target._lngLat.lat])

    map.on('click', (e) => {
        marker.setLngLat(e.lngLat)
        pickMap.currentPosition = [e.lngLat.lng, e.lngLat.lat]
    } )

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

        // Update local storage values
        console.log(pickMap.currentPosition)
        console.log(originalPosition)
        localStorage.setItem('userLocationLng', pickMap.currentPosition[0])
        localStorage.setItem('userLocationLat', pickMap.currentPosition[1])

        // Get geolocation data of this point from map provider
        var geolocation = await pickMap.getCourseGeolocation(pickMap.currentPosition)

        // Build variable to send to server
        var userLocationData = {
            lngLat: pickMap.currentPosition,
            geolocation
        }

        // Send data to server and get user location data sorted
        ajaxJsonPostRequest (pickMap.apiUrl, userLocationData, async (message) => {

            showResponseMessage(message)

            // Update location on edit page
            document.querySelector('#userLocationString').innerText = userLocationData.geolocation.city + ' (' + userLocationData.geolocation.prefecture + ')'
        } )

    }
}