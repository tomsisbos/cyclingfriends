import NeighboursMap from "/map/class/neighbours/NeighboursMap.js"
import NeighbourPopup from "/map/class/neighbours/NeighbourPopup.js"
import CFUtils from "/map/class/CFUtils.js"

var neighboursMap = new NeighboursMap()

ajaxGetRequest (neighboursMap.apiUrl + "?get-neighbours=true", async (neighbours) => {

    neighboursMap.data.neighbours = neighbours


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
        var neighbourPopup = new NeighbourPopup(neighbour,)
        marker.setPopup(neighbourPopup.popup)

        // Add hovering event listeners
        const $card = document.querySelector('#card' + neighbour.id)
        $marker.addEventListener('mouseenter', () => {
            $marker.classList.add('hover')
            $card.classList.add('hover')
            neighboursMap.displayHoverLink(neighbour)
        } )
        $marker.addEventListener('mouseleave', () => {
            $marker.classList.remove('hover')
            $card.classList.remove('hover')
            neighboursMap.hideHoverLink(neighbour)
        } )
        $card.addEventListener('mouseenter', () => {
            $marker.classList.add('hover')
            $card.classList.add('hover')
            neighboursMap.displayHoverLink(neighbour)
        } )
        $card.addEventListener('mouseleave', () => {
            $marker.classList.remove('hover')
            $card.classList.remove('hover')
            neighboursMap.hideHoverLink(neighbour)
        } )
        // Add click event listeners
        $card.addEventListener('click', () => {
            if (!$card.classList.contains('selected-marker')) {
                document.querySelectorAll('.selected-marker').forEach(element => element.classList.remove('selected-marker'))
                $card.classList.add('selected-marker')
                $marker.querySelector('img').classList.add('selected-marker')
                map._markers.forEach(marker => {
                    if (marker.getPopup() && marker.getPopup().isOpen()) marker.togglePopup()
                } )
                neighbours.forEach(nbr => {
                    if (map.getLayer('selectedLink' + nbr.id)) {
                        neighboursMap.hideSelectedLink(nbr)
                    }
                } )
                marker.togglePopup()
                neighboursMap.displaySelectedLink(neighbour)
                map.fitBounds(CFUtils.getWiderBounds([marker.getLngLat(), neighboursMap.userLocation], 2))
                neighboursMap.$map.scrollIntoView()
            } else {
                $marker.querySelector('img').classList.remove('selected-marker')
                $card.classList.remove('selected-marker')
                neighboursMap.hideSelectedLink(neighbour)
            }
        } )
        $marker.addEventListener('click', () => {
            if (!$marker.querySelector('img').classList.contains('selected-marker')) {
                document.querySelectorAll('.selected-marker').forEach(element => element.classList.remove('selected-marker'))
                $card.classList.add('selected-marker')
                $marker.querySelector('img').classList.add('selected-marker')
                neighbours.forEach(nbr => {
                    if (map.getLayer('selectedLink' + nbr.id)) {
                        neighboursMap.hideSelectedLink(nbr)
                    }
                } )
                neighboursMap.displaySelectedLink(neighbour)
                map.fitBounds(CFUtils.getWiderBounds([marker.getLngLat(), neighboursMap.userLocation], 4))
            } else {
                marker.togglePopup()
                $marker.querySelector('img').classList.remove('selected-marker')
                $card.classList.remove('selected-marker')
                neighboursMap.hideSelectedLink(neighbour)
            }
        } )

        // Add map interaction
        map.on('dragend', () => document.querySelectorAll('.nbr-marker').forEach(($mkr) => neighboursMap.scaleMarkerAccordingToZoom($mkr)) )
        map.on('zoomend', () => {
            document.querySelectorAll('.nbr-marker').forEach(($mkr) => neighboursMap.scaleMarkerAccordingToZoom($mkr))
            if (map.getZoom() > 12) {
                neighboursMap.map._markers.forEach(mkr => {
                    mkr.getElement().style.display = "none"
                    if (mkr.getPopup() && mkr.getPopup().getElement()) mkr.getPopup().getElement().style.display = "none"
                } )
                neighboursMap.displayZoomMessage()
                neighbours.forEach(neighbour => {
                    neighboursMap.hideHoverLink(neighbour)
                    neighboursMap.hideSelectedLink(neighbour)
                } )
            } else {
                neighboursMap.map._markers.forEach(mkr => {
                    mkr.getElement().style.display = "block"
                    if (mkr.getPopup() && mkr.getPopup().getElement()) mkr.getPopup().getElement().style.display = "flex"
                } )
                neighboursMap.hideZoomMessage()
            }
        } )
    } )
} )