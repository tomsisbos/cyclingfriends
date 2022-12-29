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

    setMkpoint (mkpoint) {  
        
        // Build element
        let element = document.createElement('div')
        let icon = document.createElement('img')
        icon.src = 'data:image/jpeg;base64,' + mkpoint.thumbnail
        icon.classList.add('mkpoint-icon')
        element.appendChild(icon)
        this.scaleMarkerAccordingToZoom(icon) // Set scale according to current zoom
        var marker = new mapboxgl.Marker ( {
            anchor: 'center',
            color: '#5e203c',
            draggable: false,
            element: element
        } )
        marker.setLngLat([mkpoint.lngLat.lng, mkpoint.lngLat.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'mkpoint' + mkpoint.id
        marker.getElement().classList.add('mkpoint-marker')
        marker.getElement().dataset.id = mkpoint.id
        marker.getElement().dataset.user_id = mkpoint.user.id
        this.mkpointsMarkerCollection.push(marker)

        // Build and attach popup
        var popupOptions = {}
        var instanceData = {
            mapInstance: this,
            mkpoint
        }
        var instanceOptions = {
            noSession: true
        }
        let sceneryPopup = new HomeSceneryPopup(popupOptions, instanceData, instanceOptions)
        marker.setPopup(sceneryPopup.popup)
    }

    openSegmentPopup (segment) {
        return new Promise (async (resolve, reject) => {

            // Create segment popup instance
            segment.segmentPopup = new HomeSegmentPopup( {
                anchor: 'top-left',
                className: 'js-linestring marker-popup js-segment-popup',
                focusAfterOpen: false
            }, segment, {noSession: true})

            // Prepare and display segment popup
            const popup = segment.segmentPopup.popup
            popup.setLngLat(segment.route.coordinates[0])
            popup.addTo(this.map)
            segment.segmentPopup.loadRating(segment)
            segment.segmentPopup.generateProfile({force: true})
            segment.segmentPopup.addIconButtons()
            popup.getElement().querySelector('#fly-button').addEventListener('click', async () => {
                this.map.off('moveend', this.updateMapDataListener)
                await this.flyAlong(turf.lineString(segment.route.coordinates), {layerId: 'segment' + segment.id})
                this.map.on('moveend', this.updateMapDataListener)
            } )

            // Color segment cap in hovering style
            if (this.map.getLayer('segmentCap' + segment.id)) {
                this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.capColorHover)
                this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 1)
                console.log('segment cap painted')
            } else {
                // Add segment cap layer
                this.map.addLayer( {
                    id: 'segmentCap' + segment.id,
                    type: 'line',
                    source: 'segment' + segment.id,
                    layout: {
                        'line-join': 'round',
                        'line-cap': 'round'
                    },
                    paint: {
                        'line-color': this.capColorHover,
                        'line-width': 2,
                        'line-opacity': 1,
                        'line-gap-width': 2
                    }
                } )
                console.log('segment cap added from scratch')
            }
            
            // Remove instance and hide segment cap when popup is closed
            popup.on('close', () => {
                delete segment.segmentPopup
                if (this.map.getLayer('segmentCap' + segment.id)) {
                    this.map.setPaintProperty('segmentCap' + segment.id, 'line-opacity', 0)
                    this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.segmentCapColor)
                }
            } )

            // Update segmentsCollection entry
            this.segmentsCollection.forEach((entry) => {
                if (segment.id == entry.id) entry = segment
            } )

            // Focus on segment
            this.focus(turf.lineString(segment.route.coordinates))
            
            // Add segment relevant photos
            segment.segmentPopup.mkpoints = await segment.segmentPopup.getMkpoints()
            segment.segmentPopup.photos = segment.segmentPopup.getPhotos()
            segment.segmentPopup.displayPhotos()

            resolve (popup)
        } )
    }
}