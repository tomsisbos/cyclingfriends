import Modal from "/map/class/Modal.js"
import Popup from "/map/class/Popup.js"

export default class CheckpointPopup extends Popup {

    constructor (data) {
        super()
        this.data = data
        this.popup.setHTML(this.setContent())
    }
    type = 'checkpoint'
    data

    setContent () {
        // Build thumbnail src
        if (this.data.img.blob) this.data.img.src = 'data:' + this.data.img.type + ';base64,' + this.data.img.blob
        else this.data.img.src = '/media/default-photo-' + Math.ceil(Math.random() * 9) + '.svg'
        // Set thumbnail if there is one
        if (this.data.img.src) { var thumbnailContent = `
            <div class="popup-img-container">
            <img class="popup-img" src="` + this.data.img.src + `" />
                <div class="popup-icons">
                    <div id="target-button" title="この位置に移動する">
                        <span class="iconify" data-icon="icomoon-free:target" data-width="20" data-height="20"></span>
                    </div>
                </div>
            </div>`
        } else var thumbnailContent = ''

        // Return HTML content
        return thumbnailContent + `
        <div class="checkpointMarkerForm">
            <div class="checkpoint-popup-line">
                <div>
                    <span class="bold">` + this.data.name +  `</span> (km ` +
                    Math.floor(parseFloat(this.data.distance) * 10) / 10 + `)
                </div>
                <div>
                    alt. ` + this.data.elevation + `m
                </div>
            </div>
            <div class="checkpoint-popup-line">
                <div>` + this.data.description + `</div>
            </div>
        </div>`
    }

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

    setModal () {
        var modal = new Modal(this.data.img.src)
        var img = this.popup._content.querySelector('.popup-img')
        document.body.appendChild(modal.element)
        img.addEventListener('click', () => modal.open())
    }
}