import CFUtils from "/map/class/CFUtils.js"

const exportButton = document.querySelector('#export')

ajaxGetRequest ('/actions/routes/api.php' + "?route-load=" + exportButton.dataset.id, async (route) => {
    // Build route geojson
    var coordinates = []
    route.coordinates.forEach( (coordinate) => {
        coordinates.push([parseFloat(coordinate.lng), parseFloat(coordinate.lat)])
    } )
    var geojson = {
        type: 'Feature',
        properties: {
            tunnels: route.tunnels
        },
        geometry: {
            type: 'LineString',
            coordinates: coordinates
        }
    }
    exportButton.href = CFUtils.loadGpx(geojson)
    exportButton.download = route.name + '.gpx'
} )