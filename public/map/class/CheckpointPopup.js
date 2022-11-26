import Popup from "/map/class/Popup.js"

export default class CheckpointPopup extends Popup {

    constructor () {
        super()
    }
    type = 'checkpoint'
    data

    setTarget = () => {
        this.popup.getElement().querySelector('#target-button').addEventListener('click', () => {
            var map = this.popup._map
            var lngLat = this.popup._lngLat
            map.flyTo( {
                center: lngLat,
                zoom: 17,
                speed: 0.4,
                curve: 1,
                pitch: 40,
                easing(t) {
                return t
                }
            } )
        } )
    }
    
    select () {
        document.querySelector('.mapboxgl-canvas-container #checkpoint' + this.data.number).classList.add('selected-marker')
        document.querySelector('#checkpointPoiIcon' + this.data.number).classList.add('selected-marker')
        if (document.querySelector('.spec-table #checkpoint' + this.data.number)) document.querySelector('.spec-table #checkpoint' + this.data.number).classList.add('selected-entry')
    }
}