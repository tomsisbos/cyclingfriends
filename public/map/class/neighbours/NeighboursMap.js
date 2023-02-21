import GlobalMap from "/map/class/GlobalMap.js"

// Global class initialization
export default class NeighboursMap extends GlobalMap {

    constructor () {
        super()
    }

    apiUrl = '/api/riders/neighbours.php'
    $map = document.querySelector('#neighboursMap')
    defaultZoom = 8
    data = {}
    
    centerOnUserLocation () {
        if (this.map) this.map.setCenter(this.userLocation)
    }

    buildMarkerElement (neighbour) {
        const $marker = document.createElement('div')
        $marker.id = 'marker' + neighbour.id
        $marker.classList = 'nbr-marker'
        const $markerImg = document.createElement('img')
        $markerImg.src = neighbour.propic
        $marker.appendChild($markerImg)
        return $marker
    }

    scaleMarkerAccordingToZoom (element) {
        var zoom = this.map.getZoom()
        var size = (zoom * 3 - 10)
        if (size < 8) size = 8
        element.style.height = size + 'px'
        element.style.width = size + 'px'
    }

    displayHoverLink (neighbour) {
        var link = turf.lineString([[neighbour.lngLat.lng, neighbour.lngLat.lat], [this.userLocation.lng, this.userLocation.lat]])
        this.map.addLayer( {
            id: 'hoverLink' + neighbour.id,
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
        }, 'no-bicycle-rindos')
    }

    displaySelectedLink (neighbour) {
        var link = turf.lineString([[neighbour.lngLat.lng, neighbour.lngLat.lat], [this.userLocation.lng, this.userLocation.lat]])
        console.log(link)
        this.map.addLayer( {
            id: 'selectedLink' + neighbour.id,
            type: 'line',
            source: {
                type: 'geojson',
                data: link
            },
            layout: {},
            paint: {
                'line-color': '#ff5555',
                'line-width': 3,
                'line-dasharray': [3, 2],
                'line-opacity': 1,
            }
        } )
    }

    hideHoverLink (neighbour) {
        if (this.map.getLayer('hoverLink' + neighbour.id)) {
            this.map.removeLayer('hoverLink' + neighbour.id)
            this.map.removeSource('hoverLink' + neighbour.id)
        }
    }
    
    hideSelectedLink (neighbour) {
        if (this.map.getLayer('selectedLink' + neighbour.id)) {
            this.map.removeLayer('selectedLink' + neighbour.id)
            this.map.removeSource('selectedLink' + neighbour.id)
        }
    }

    displayZoomMessage () {
        if (!this.$map.querySelector('.alert-modal')) {
            var modal = document.createElement('div')
            modal.className = "alert-modal"
            var messageWindow = document.createElement('div')
            messageWindow.className = 'alert-window'
            messageWindow.innerText = "プライバシーの都合上、このズームレベルではユーザーの位置情報が表示されません。"
            modal.appendChild(messageWindow)
            this.$map.appendChild(modal)
        }
    }

    hideZoomMessage () {
        if (this.$map.querySelector('.alert-modal')) this.$map.querySelector('.alert-modal').remove()
    }

}