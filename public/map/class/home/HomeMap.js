import CFUtils from "/map/class/CFUtils.js"
import WorldMap from "/map/class/WorldMap.js"
import HomeSceneryPopup from "/map/class/home/HomeSceneryPopup.js"
import HomeSegmentPopup from "/map/class/home/HomeSegmentPopup.js"

export default class HomeMap extends WorldMap {

    constructor (options) {
        super(options)
    }

    apiUrl = '/api/home.php'
    segmentColor = '#ff5555'
    defaultCenter = [139.2056, 35.613002]
    sceneryLoader = {
        prepare: () => {
            this.loaderElement = document.createElement('div')
            this.loaderElement.className = 'loading-modal-relative'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerText = '絶景スポットのデータを準備中...'
            this.loaderElement.style.cursor = 'loading'
            loaderIcon.className = 'loading-text'
            this.loaderElement.appendChild(loaderIcon)
        },
        start: () => this.$map.appendChild(this.loaderElement),
        stop: () => this.loaderElement.remove()
    }
    segmentLoader = {
        prepare: () => {
            this.loaderElement = document.createElement('div')
            this.loaderElement.className = 'loading-modal-relative'
            let loaderIcon = document.createElement('div')
            loaderIcon.innerText = 'セグメントのデータを準備中...'
            this.loaderElement.style.cursor = 'loading'
            loaderIcon.className = 'loading-text'
            this.loaderElement.appendChild(loaderIcon)
        },
        start: () => this.$map.appendChild(this.loaderElement),
        stop: () => this.loaderElement.remove()
    }

    setScenery (scenery) {  
        
        // Build element
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + scenery.thumbnail
        icon.classList.add('scenery-icon')
        element.appendChild(icon)
        this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )
        marker.setLngLat([scenery.lng, scenery.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'scenery' + scenery.id
        marker.getElement().classList.add('scenery-marker')
        marker.getElement().dataset.id = scenery.id
        marker.getElement().dataset.user_id = scenery.user_id
        this.sceneriesMarkerCollection.push(marker)

        // Build and attach popup
        var popupOptions = {
            focusAfterOpen: false
        }
        var instanceData = {
            mapInstance: this,
            scenery
        }
        var instanceOptions = {
            noSession: true
        }
        let sceneryPopup = new HomeSceneryPopup(popupOptions, instanceData, instanceOptions)
        marker.setPopup(sceneryPopup.popup)
    }
    
    updateMapData () {
        this.updateSegments()
        this.openSegmentPopup(this.randomSegment)
    }

    updateSegments () {
        this.mapdata.segments.forEach(segment => {
            this.segmentsCollection.push(segment)
            if (!this.map.getSource('segment' + segment.id)) this.displaySegment(segment)
        } )
    }

    openSegmentPopup (segment) {
        return new Promise (async (resolve, reject) => {
            
            // Create segment popup instance
            if (!segment.segmentPopup) {
                segment.mapInstance = this
                segment.segmentPopup = new HomeSegmentPopup( {
                    closeOnClick: true,
                    focusAfterOpen: false,
                    anchor: 'top-left',
                    className: 'js-linestring marker-popup js-segment-popup'
                }, segment, {noSession: true})
                // Prepare and display segment popup
                const popup = segment.segmentPopup.popup
                popup.setLngLat(segment.coordinates[0])
                popup.addTo(this.map)
                segment.segmentPopup.setFlyAlong = (flyAlongButton) => {
                    flyAlongButton.addEventListener('click', async () => {
                        this.map.off('moveend', this.updateMapDataListener)
                        await this.flyAlong(turf.lineString(segment.coordinates), {layerId: 'segment' + segment.id})
                        this.map.on('moveend', this.updateMapDataListener)
                    } )
                }
                
                // Hide segment cap when popup is closed
                popup.on('close', () => {
                    if (this.map.getLayer('segmentCap' + segment.id)) {
                        this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 0)
                        this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.segmentCapColor)
                    }
                } )
            } else {
                segment.segmentPopup.popup.setLngLat(segment.coordinates[0])
                segment.segmentPopup.popup.addTo(this.map)
            }

            // Color segment cap in hovering style
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.capColorHover)

            // Update segmentsCollection entry
            this.segmentsCollection.forEach((entry) => {
                if (segment.id == entry.id) entry = segment
            } )

            // Focus on segment
            this.focus(this.map.getSource('segment' + segment.id)._data)

            resolve(segment.segmentPopup.popup)
        } )
    }

    // Set another map style without interfering with current route
    setMapStyle (layerId) {

        // Change map style
        this.map.setStyle('mapbox://styles/sisbos/' + layerId).once('idle', async () => {
            this.loadImages()
            this.addSources()
            this.addLayers()
        } )
    }
}