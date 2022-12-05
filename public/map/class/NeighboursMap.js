import GlobalMap from "/map/class/GlobalMap.js"

// Global class initialization
export default class NeighboursMap extends GlobalMap {

    constructor () {
        super()
    }

    apiUrl = '/api/riders/neighbours.php'
    $map = document.querySelector('#neighboursMap')
    defaultZoom = 8
    
    centerOnUserLocation () {
        this.map.setCenter(this.userLocation)
    }

    buildMarkerElement (neighbour) {
        const $marker = document.createElement('div')
        $marker.id = 'narker' + neighbour.id
        $marker.classList = 'nbr-marker'
        const $markerImg = document.createElement('img')
        $markerImg.src = neighbour.propic
        $marker.appendChild($markerImg)
        return $marker
    }

    displayLink (neighbour) {
        var link = turf.lineString([[neighbour.lngLat.lng, neighbour.lngLat.lat], [this.userLocation.lng, this.userLocation.lat]])
        console.log(link)
        this.map.addLayer( {
            id: 'link' + neighbour.id,
            type: 'line',
            source: {
                type: 'geojson',
                data: link
            },
            layout: {},
            paint: {
                'line-color': '#00e06e',
                'line-width': 3,
                'line-dasharray': [3, 2],
                'line-opacity': 1,
            }
        } )
        console.log(this.map.getLayer('link' + neighbour.id))
    }

    hideLink (neighbour) {
        this.map.removeLayer('link' + neighbour.id)
        this.map.removeSource('link' + neighbour.id)
    }

}