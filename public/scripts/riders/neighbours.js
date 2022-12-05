import NeighboursMap from "../../map/class/NeighboursMap"

var neighboursMap = new NeighboursMap()
console.log(neighboursMap)

ajaxGetRequest (neighboursMap.apiUrl + "?get-neighbours=true", async (neighbours) => {

    // Setup map
    var map = await neighboursMap.load(neighboursMap.$map, neighboursMap.defaultStyle)
    neighboursMap.addSources()
    neighboursMap.addLayers()

    // Add connected user marker
    ajaxGetRequest ('/api/map.php' + "?getpropic=true", (src) => {
        let neighbour = {
            id: neighboursMap.session.id,
            propic: src
        }
        var $marker = neighboursMap.buildMarkerElement(neighbour)
        var $markerImg = $marker.querySelector('img')
        $markerImg.classList.add('admin-marker')
        const marker = new mapboxgl.Marker($marker)
        marker.setLngLat(neighboursMap.userLocation)
        marker.addTo(map)
    } )
    
    // Add all neighbours markers
    neighbours.forEach( (neighbour) => {
        var $marker = neighboursMap.buildMarkerElement(neighbour)
        const marker = new mapboxgl.Marker($marker)
        marker.setLngLat(neighbour.lngLat)
        marker.addTo(map)

        // Add event listeners
        const $card = document.querySelector('#card' + neighbour.id)
        $marker.addEventListener('mouseenter', () => {
            $marker.classList.add('hover')
            $card.classList.add('hover')
            neighboursMap.displayLink(neighbour)
        } )
        $marker.addEventListener('mouseleave', () => {
            $marker.classList.remove('hover')
            $card.classList.remove('hover')
            neighboursMap.hideLink(neighbour)
        } )
        $card.addEventListener('mouseenter', () => {
            $marker.classList.add('hover')
            $card.classList.add('hover')
            neighboursMap.displayLink(neighbour)
        } )
        $card.addEventListener('mouseleave', () => {
            $marker.classList.remove('hover')
            $card.classList.remove('hover')
            neighboursMap.hideLink(neighbour)
        } )
    } )

} )