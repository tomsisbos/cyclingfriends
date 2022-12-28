import WorldMap from "/map/class/WorldMap.js"
import HomeMkpointPopup from "/map/class/HomeMkpointPopup.js"
import HomeSegmentPopup from "/map/class/HomeSegmentPopup.js"

export default class HomeMap extends WorldMap {

    constructor (session) {
        super(session)
    }

    segmentColor = '#ff5555'

    sceneryLoader = {
        prepare: () => {
            console.log(this.$map)
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
            console.log(this.$map)
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
        // Add marker to the map and to markers collection
        let mkpointPopup = new HomeMkpointPopup()
        var content = mkpointPopup.setPopupContent(mkpoint)
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

        let popup = mkpointPopup.popup
        popup.setHTML(content)
        mkpointPopup.data = mkpoint
        marker.setPopup(popup)
        marker.setLngLat([mkpoint.lngLat.lng, mkpoint.lngLat.lat])
        marker.addTo(this.map)
        marker.getElement().id = 'mkpoint' + mkpoint.id
        marker.getElement().classList.add('mkpoint-marker')
        marker.getElement().dataset.id = mkpoint.id
        marker.getElement().dataset.user_id = mkpoint.user.id
        popup.once('open', async (e) => {
            // Add 'selected-marker' class to selected marker
            this.unselect()
            mkpointPopup.select()
            mkpointPopup.reviews()
            mkpointPopup.rating()
            mkpointPopup.setTarget()
            mkpointPopup.addPhoto()
        } )
        popup.on('close', (e) => {
            // Remove 'selected-marker' class from selected marker if there is one
            if (document.getElementById('mkpoint' + mkpointPopup.data.id)) {
                document.getElementById('mkpoint' + mkpointPopup.data.id).querySelector('.mkpoint-icon').classList.remove('selected-marker')
            }
        } )
        this.mkpointsMarkerCollection.push(marker)
    }

    openSegmentPopup (segment) {
        return new Promise (async (resolve, reject) => {

            // Create segment popup instance
            segment.segmentPopup = new HomeSegmentPopup( {
                closeOnClick: true,
                anchor: 'bottom',
                className: 'js-linestring marker-popup js-segment-popup'
            }, segment)

            // Prepare and display segment popup
            const popup = segment.segmentPopup.popup
            popup.setLngLat(segment.route.coordinates[0])
            popup.addTo(this.map)
            segment.segmentPopup.rating()
            segment.segmentPopup.generateProfile({force: true})
            segment.segmentPopup.addIconButtons()
            popup.getElement().querySelector('#fly-button').addEventListener('click', async () => {
                this.map.off('moveend', this.updateMapDataListener)
                await this.flyAlong(turf.lineString(segment.route.coordinates), {layerId: 'segment' + segment.id})
                this.map.on('moveend', this.updateMapDataListener)
            } )

            // Color segment cap in hovering style
            this.map.setPaintProperty('segmentCap' + segment.id, 'line-color', this.capColorHover)
            
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
            this.focus(this.map.getSource('segment' + segment.id)._data)
            
            // Add segment relevant photos
            segment.segmentPopup.mkpoints = await segment.segmentPopup.getMkpoints()
            segment.segmentPopup.photos = segment.segmentPopup.getPhotos()
            segment.segmentPopup.displayPhotos()

            resolve (popup)
        } )
    }
}