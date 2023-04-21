import CFUtils from "/map/class/CFUtils.js"
import HomeMap from "/map/class/home/HomeMap.js"

var $map = document.querySelector('#homeSegmentMap')
var loaded = false

// Specific ajax GET request function to home page (custom headers)
function ajaxGetRequest (url, callback, loader = null) {
	var xhr = ajax(callback, loader)
	// Send request through POST method
	xhr.open('GET', url, true)
	xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest')
	xhr.setRequestHeader('Access-Control-Allow-Origin', 'cyclingfriends/home')
	xhr.send()
}

// Load map when user scrolls down to container

async function loadMap () {
    return new Promise((resolve, reject) => {
        $map.addEventListener('click', async () => {
            if (!loaded) {
                $map.classList.remove('click-map')
                var homeMap = new HomeMap({noSession: true})
                loaded = true
                await homeMap.load($map, 'mapbox://styles/sisbos/cl07xga7c002616qcbxymnn5z')
                resolve(homeMap)
            }
        } )
    } )
}

await loadMap().then( (homeMap) => {

    // Load CF sources and layers
    homeMap.addSources()
    homeMap.addLayers()

    // Add controls
    homeMap.addStyleControl()
    homeMap.addFilterControl()

    // Prepare and display segments data
    ajaxGetRequest (homeMap.apiUrl + "?display-segments=true", async (segments) => {
        homeMap.mapdata.segments = segments

        // Choose a segment at random
        var randomKey = Math.floor(Math.random() * segments.length)
        homeMap.randomSegment = segments[randomKey]
        const rsCoordinates = homeMap.randomSegment.coordinates

        // Fly to it
        homeMap.map.jumpTo( {
            center: rsCoordinates[0],
            pitch: 45,
        } )
        const rsBounds = CFUtils.defineRouteBounds(rsCoordinates)
        homeMap.map.fitBounds(rsBounds).once('idle', () => {
            homeMap.updateSegments()
            homeMap.openSegmentPopup(homeMap.randomSegment)
        } )
    }, homeMap.segmentLoader)

} )