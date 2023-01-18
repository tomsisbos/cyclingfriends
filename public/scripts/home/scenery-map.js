import HomeMap from "/map/class/home/HomeMap.js"

var $map = document.querySelector('#homeSceneryMap')
var loaded = false

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

    // Prepare and display mkpoints data
    ajaxGetRequest (homeMap.apiUrl + "?display-mkpoints=details", async (mkpoints) => {
        homeMap.data.mkpoints = mkpoints
        homeMap.updateMkpoints()

        // Choose a scenery at random
        var randomKey = Math.floor(Math.random() * mkpoints.length)
        var randomMkpoint = mkpoints[randomKey]
        const rmCoordinates = randomMkpoint.lngLat

        // Fly to it
        homeMap.map.jumpTo( {
            center: [rmCoordinates.lng, rmCoordinates.lat],
            zoom: 14,
            pitch: 75,
            bearing: 20
        } )

        // Open popup
        var marker = await homeMap.getMkpointMarker(randomMkpoint)
        marker.togglePopup()

    }, homeMap.sceneryLoader)

    // Update map data on ending moving the map
    homeMap.map.on('moveend', () => {
        homeMap.updateMkpoints()
    } )

} )