import Model from "/map/class/Model.js"

export default class Popup extends Model {

    constructor (src) {
        super()

        this.src = src
        var modal = document.createElement('div')
        modal.className = 'modal'

        // Load lightbox script for this popup
        var script = document.createElement('script')
        script.src = '/assets/js/lightbox-script.js'
        modal.appendChild(script)

        // Prepare content
        modal.innerHTML = `
            <span class="close cursor">&times;</span>
            <div class="modal-block">
                <div class="propicSlide">
                    <img src="` + this.src + `" style="width:100%">
                </div>
            </div>
        `
        // Close functionality
        modal.querySelector('.close.cursor').addEventListener('click', () => this.close())
        modal.addEventListener('click', (e) => {
            var eTarget = e ? e.target : event.srcElement
            if ((eTarget == this.popup) || (eTarget == modal)) modal.style.display = 'none'
        } )

        this.element = modal
        return this
    }

    src
    element

    open = () => this.element.style.display = 'flex'
    close = () => this.element.style.display = 'none'



}